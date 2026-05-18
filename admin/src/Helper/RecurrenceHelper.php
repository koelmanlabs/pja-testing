<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * Handles all recurrence logic: calculation, generation, dissolving and display.
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Recurrence types
 */
final class RecurrenceType
{
    const NONE     = 0;
    const DAILY    = 1;
    const WEEKLY   = 2;
    const MONTHLY  = 3;
    const WEEKDAY  = 4;
    const YEARLY   = 5;
    const LASTDAY  = 6;
}

/**
 * RecurrenceHelper — pure static service class.
 * Replaces the scattered recurrence logic from \PlanjeagendaHelper::cleanup(),
 * calculate_recurrence(), dissolve_recurrence() and the edit.php template.
 */
class RecurrenceHelper
{
    // ── Public API ───────────────────────────────────────────────────────────

    /**
     * Generate new recurrence occurrences for all active recurring events.
     * Called after save (forced) or daily (lazy).
     *
     * @param  int  $forced  0=lazy (only if new day), 2=force on save
     */
    public static function generate(int $forced = 0): void
    {
        $config        = \PlanjeagendaConfig::getInstance();
        $jemsettings   = \PlanjeagendaHelper::config();
        $weekstart     = (int) ($jemsettings->weekdaystart ?? 1);
        $now           = time();
        $offset        = idate('Z');
        $lastupdate    = (int) ($jemsettings->lastupdate ?? 0);
        $runningupdate = (int) ($jemsettings->runningupdate ?? 0);
        $maxexectime   = (int) ini_get('max_execution_time');
        $delay         = min(86400, max(300, $maxexectime * 2));

        $nrdaysnow    = floor(($now + $offset) / 86400);
        $nrdaysupdate = floor(($lastupdate + $offset) / 86400);

        if (!$forced && $nrdaysnow <= $nrdaysupdate) {
            return; // nothing to do today
        }

        if (!$forced && ($runningupdate + $delay) >= $now) {
            return; // another process is running
        }

        $config->set('runningupdate', $now);

        // Fire plugin event
        PluginHelper::importPlugin('planjeagenda');
        Factory::getApplication()->triggerEvent('onJemBeforeCleanup', [$jemsettings, $forced]);

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = '  SELECT id,
                           CASE recurrence_first_id WHEN 0 THEN id ELSE recurrence_first_id END AS first_id,
                           recurrence_number, recurrence_type, recurrence_limit_date,
                           recurrence_limit, recurrence_byday, recurrence_bylastday,
                           MAX(dates) AS dates, MAX(enddates) AS enddates,
                           MAX(recurrence_counter) AS counter
                    FROM   #__pja_events
                    WHERE  recurrence_type <> "0"
                    AND    CASE WHEN recurrence_limit_date IS NULL THEN 1 ELSE NOW() < recurrence_limit_date END
                    AND    recurrence_number <> "0"
                    GROUP  BY first_id
                    ORDER  BY dates DESC';

        $db->setQuery($query);
        $rows = $db->loadAssocList() ?: [];

        foreach ($rows as $row) {
            $row['weekstart'] = $weekstart;
            self::generateForRow($row, $jemsettings, $db);
        }

        // Housekeeping: delete / archive old events
        self::archiveOldEvents($jemsettings, $db);

        $config->set('lastupdate', $now);
        $config->set('runningupdate', 0);
    }

    /**
     * Dissolve a recurrence series: reset all child events to standalone.
     */
    public static function dissolve(int $first_id): bool
    {
        if ($first_id <= 0) {
            return false;
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery(
                'UPDATE #__pja_events
                 SET    recurrence_first_id = 0, recurrence_type = 0,
                        recurrence_counter  = 0, recurrence_number = 0,
                        recurrence_limit    = 0, recurrence_limit_date = NULL,
                        recurrence_byday    = ' . $db->quote('')  . '
                 WHERE  recurrence_first_id = ' . $first_id
            );
            $db->execute();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the next occurrence date for a recurrence row.
     *
     * @param  array  $row  Recurrence row with dates, enddates, recurrence_type, etc.
     * @return array        Updated row with new dates/enddates, or false on error.
     */
    public static function calculateNext(array $row): array|false
    {
        $type   = (int) $row['recurrence_type'];
        $number = (int) $row['recurrence_number'];
        $parsed = self::parseDates($row['dates'], $row['enddates']);

        $startDay = match ($type) {
            RecurrenceType::DAILY   => self::calcDaily($parsed, $number),
            RecurrenceType::WEEKLY  => self::calcWeekly($parsed, $number),
            RecurrenceType::MONTHLY => self::calcMonthly($parsed, $number),
            RecurrenceType::WEEKDAY => self::calcWeekday($parsed, $number, $row),
            RecurrenceType::YEARLY  => self::calcYearly($parsed, $number),
            RecurrenceType::LASTDAY => self::calcLastDay($parsed, $number, $row['recurrence_bylastday'] ?? ''),
            default                 => false,
        };

        if (!$startDay) {
            return false;
        }

        if ($startDay < $parsed['unixtime']) {
            throw new \RuntimeException(
                Text::_('COM_KLEVENTS_RECURRENCE_DATE_GENERATION_ERROR'), 500
            );
        }

        $row['dates']    = date('Y-m-d', $startDay);
        $row['enddates'] = $parsed['dayDiff'] > 0
            ? date('Y-m-d', $startDay + $parsed['dayDiff'])
            : $row['enddates'];

        return $row;
    }

    /**
     * Return a human-readable description of a recurrence setting.
     *
     * @param  object|array  $item  Event item or recurrence data.
     */
    public static function describe($item): string
    {
        $item = (object) $item;
        $type    = (int) ($item->recurrence_type ?? 0);
        $number  = $item->recurrence_number ?? 0;
        $byday   = $item->recurrence_byday ?? '';
        $bylast  = $item->recurrence_bylastday ?? '';
        $rldate  = $item->recurrence_limit_date ?? null;
        $limitStr = $rldate ? self::formatLimitDate($rldate) : Text::_('com_planjeagenda_UNLIMITED');

        $typeLabel = match ($type) {
            RecurrenceType::DAILY   => Text::_('com_planjeagenda_DAILY'),
            RecurrenceType::WEEKLY  => Text::_('com_planjeagenda_WEEKLY'),
            RecurrenceType::MONTHLY => Text::_('com_planjeagenda_MONTHLY'),
            RecurrenceType::WEEKDAY => Text::_('com_planjeagenda_WEEKDAY'),
            RecurrenceType::YEARLY  => Text::_('com_planjeagenda_YEARLY'),
            RecurrenceType::LASTDAY => Text::_('com_planjeagenda_LASTDAY'),
            default                 => '',
        };

        $detail = match ($type) {
            RecurrenceType::DAILY   => str_replace('[placeholder]', $number, Text::_('com_planjeagenda_OUTPUT_DAY')),
            RecurrenceType::WEEKLY  => str_replace('[placeholder]', $number, Text::_('com_planjeagenda_OUTPUT_WEEK')),
            RecurrenceType::MONTHLY => str_replace('[placeholder]', $number, Text::_('com_planjeagenda_OUTPUT_MONTH')),
            RecurrenceType::YEARLY  => str_replace('[placeholder]', $number, Text::_('com_planjeagenda_OUTPUT_YEAR')),
            RecurrenceType::WEEKDAY => self::describeWeekday($number, $byday),
            RecurrenceType::LASTDAY => self::describeLastday($number, $bylast),
            default                 => '',
        };

        return trim("$typeLabel — $detail (t/m $limitStr)");
    }

    /**
     * Get the anticipation (months ahead to pre-generate) for a recurrence type.
     */
    public static function anticipation(int $type, object $settings): int
    {
        return (int) min(36, match ($type) {
            RecurrenceType::DAILY   => $settings->recurrence_anticipation_day   ?? 3,
            RecurrenceType::WEEKLY  => $settings->recurrence_anticipation_week  ?? 3,
            RecurrenceType::MONTHLY => $settings->recurrence_anticipation_month ?? 6,
            RecurrenceType::WEEKDAY => $settings->recurrence_anticipation_week  ?? 3,
            RecurrenceType::YEARLY  => $settings->recurrence_anticipation_year  ?? 24,
            RecurrenceType::LASTDAY => $settings->recurrence_anticipation_lastday ?? 6,
            default                 => 3,
        });
    }

    // ── Private: generation ──────────────────────────────────────────────────

    private static function generateForRow(array $row, object $settings, $db): void
    {
        $anticipation = self::anticipation((int) $row['recurrence_type'], $settings);
        $shield       = strtotime('now + ' . $anticipation . ' month');

        // Load reference event data
        $db->setQuery('SELECT * FROM #__pja_events WHERE id = ' . (int) $row['id']);
        $reference = $db->loadAssoc();
        if (!$reference) return;

        if ($reference['published'] != 0) {
            $reference['published'] = 1;
        }

        $row = self::calculateNext($row);

        while (
            $row !== false
            && ($row['recurrence_limit_date'] === null || strtotime($row['dates']) <= strtotime($row['recurrence_limit_date']))
            && strtotime($row['dates']) <= $shield
        ) {
            $dbInst  = Factory::getContainer()->get('DatabaseDriver');
            $newEvent = new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\EventTable($dbInst);
            $ignore   = ['id', 'hits', 'dates', 'enddates', 'checked_out_time', 'checked_out'];
            $newEvent->bind($reference, $ignore);
            $newEvent->recurrence_first_id = $row['first_id'];
            $newEvent->recurrence_counter  = (int) $row['counter'] + 1;
            $newEvent->dates               = $row['dates'];
            $newEvent->enddates            = $row['enddates'];
            $newEvent->_autocreate         = true;

            if ($newEvent->store()) {
                $row['counter']++;
                // Duplicate category relations
                // Single-category: copy catid directly from pja_events
                // Copy catid using JOIN to avoid MySQL's same-table update restriction
                $db->setQuery(
                    'UPDATE #__pja_events SET catid = '
                    . '(SELECT catid FROM (SELECT catid FROM #__pja_events WHERE id = ' . (int) $row['id'] . ') AS src) '
                    . 'WHERE id = ' . (int) $newEvent->id
                );
                $db->execute();
            }

            $row = self::calculateNext($row);
        }
    }

    private static function archiveOldEvents(object $settings, $db): void
    {
        if (!isset($settings->oldevent) || !isset($settings->minus)) return;

        $interval = 'DATE_SUB(NOW(), INTERVAL ' . (int) $settings->minus . ' DAY)';
        $condition = "dates > 0 AND $interval > IF(enddates IS NOT NULL, enddates, dates)";

        if ($settings->oldevent == 1) {
            $db->setQuery("DELETE FROM #__pja_events WHERE $condition");
            $db->execute();
        } elseif ($settings->oldevent == 2) {
            $db->setQuery("UPDATE #__pja_events SET published = 2 WHERE $condition AND published = 1");
            $db->execute();
        }
    }

    // ── Private: date arithmetic ─────────────────────────────────────────────

    private static function parseDates(string $startDate, ?string $endDate): array
    {
        $unix = strtotime($startDate);
        return [
            'year'     => (int) date('Y', $unix),
            'month'    => (int) date('n', $unix),
            'day'      => (int) date('j', $unix),
            'weekday'  => (int) date('w', $unix),
            'unixtime' => $unix,
            'dayDiff'  => $endDate ? (int) round((strtotime($endDate) - $unix) / 86400) : 0,
        ];
    }

    private static function calcDaily(array $d, int $n): int
    {
        return mktime(1, 0, 0, $d['month'], $d['day'], $d['year']) + $n * 86400;
    }

    private static function calcWeekly(array $d, int $n): int
    {
        return mktime(1, 0, 0, $d['month'], $d['day'], $d['year']) + $n * 7 * 86400;
    }

    private static function calcMonthly(array $d, int $n): int
    {
        $ts = mktime(1, 0, 0, $d['month'] + $n, $d['day'], $d['year']);
        $i  = 1;
        while (date('j', $ts) != $d['day'] && $i < 20) {
            $i++;
            $ts = mktime(1, 0, 0, $d['month'] + $n * $i, $d['day'], $d['year']);
        }
        return $ts;
    }

    private static function calcYearly(array $d, int $n): int
    {
        return mktime(1, 0, 0, $d['month'], $d['day'], $d['year'] + $n);
    }

    private static function calcWeekday(array $d, int $n, array $row): int|false
    {
        $dayNames  = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
        $ordinals  = ['first','second','third','fourth','fifth'];
        $selected  = \PlanjeagendaHelper::convert2CharsDaysToInt(
            explode(',', $row['recurrence_byday'] ?? ''), 0
        );

        if (empty($selected)) {
            $selected = [$d['weekday']];
        }

        $startDay = null;

        foreach ($selected as $s) {
            [$next, $nextMonth] = match (true) {
                $n === 7 => [
                    strtotime('previous ' . $dayNames[$s] . ' - 1 week', mktime(1,0,0,$d['month']+1,1,$d['year'])),
                    strtotime('previous ' . $dayNames[$s] . ' - 1 week', mktime(1,0,0,$d['month']+2,1,$d['year'])),
                ],
                $n === 6 => [
                    strtotime('previous ' . $dayNames[$s], mktime(1,0,0,$d['month']+1,1,$d['year'])),
                    strtotime('previous ' . $dayNames[$s], mktime(1,0,0,$d['month']+2,1,$d['year'])),
                ],
                $n === 5 => self::calcFifthWeekday($d, $s, $dayNames),
                default  => [
                    strtotime($ordinals[$n-1] . ' ' . $dayNames[$s] . ' of this month', mktime(1,0,0,$d['month'],  1,$d['year'])),
                    strtotime($ordinals[$n-1] . ' ' . $dayNames[$s] . ' of this month', mktime(1,0,0,$d['month']+1,1,$d['year'])),
                ],
            };

            if ($next && $next > $d['unixtime'] && (!$startDay || $startDay > $next)) {
                $startDay = $next;
            }
            if (isset($nextMonth) && $nextMonth && (!$startDay || $startDay > $nextMonth)) {
                $startDay = $nextMonth;
            }
        }

        return $startDay ?? false;
    }

    private static function calcFifthWeekday(array $d, int $s, array $dayNames): array
    {
        $month = $d['month'];
        do {
            $firstDay  = mktime(1, 0, 0, $month, 1, $d['year']);
            $lastDay   = mktime(23, 59, 59, $month + 1, 0, $d['year']);
            $candidate = strtotime('fifth ' . $dayNames[$s] . ' of this month', $firstDay);
            $month++;
        } while ($candidate > $lastDay || $candidate < $d['unixtime']);

        return [$candidate, null];
    }

    private static function calcLastDay(array $d, int $n, string $bylastday): int
    {
        $lastdayMap  = ['L1','L2','L3','L4','L5','L6','L7'];
        $lastdayNum  = array_search($bylastday, $lastdayMap);
        $firstOfNext = mktime(1, 0, 0, $d['month'] + $n, 1, $d['year']);
        $lastOfMonth = (int) date('t', $firstOfNext);
        $targetDay   = $lastOfMonth - ($lastdayNum === false ? 0 : (int) $lastdayNum);
        return mktime(1, 0, 0, $d['month'] + $n, $targetDay, $d['year']);
    }

    // ── Private: display helpers ─────────────────────────────────────────────

    private static function describeWeekday(int $n, string $byday): string
    {
        $dayMap = [
            'MO' => Text::_('com_planjeagenda_MONDAY'),
            'TU' => Text::_('com_planjeagenda_TUESDAY'),
            'WE' => Text::_('com_planjeagenda_WEDNESDAY'),
            'TH' => Text::_('com_planjeagenda_THURSDAY'),
            'FR' => Text::_('com_planjeagenda_FRIDAY'),
            'SA' => Text::_('com_planjeagenda_SATURDAY'),
            'SU' => Text::_('com_planjeagenda_SUNDAY'),
        ];
        $numMap = [
            5 => Text::_('com_planjeagenda_LAST'),
            6 => Text::_('com_planjeagenda_BEFORE_LAST'),
        ];
        $days   = implode(', ', array_map(fn($d) => $dayMap[trim($d)] ?? $d, explode(',', $byday)));
        $numStr = $numMap[$n] ?? (string) $n;
        return str_replace(['[placeholder]','[placeholder_weekday]'], [$numStr, $days],
            Text::_('com_planjeagenda_OUTPUT_WEEKDAY'));
    }

    private static function describeLastday(int $n, string $bylastday): string
    {
        $lastMap = [
            'L1' => Text::_('com_planjeagenda_LAST_DAY'),
            'L2' => Text::_('com_planjeagenda_LAST_DAY_SECOND'),
            'L3' => Text::_('com_planjeagenda_LAST_DAY_THIRD'),
            'L4' => Text::_('com_planjeagenda_LAST_DAY_FOURTH'),
            'L5' => Text::_('com_planjeagenda_LAST_DAY_FIFTH'),
            'L6' => Text::_('com_planjeagenda_LAST_DAY_SIXTH'),
            'L7' => Text::_('com_planjeagenda_LAST_DAY_SEVEN'),
        ];
        $lastStr = $lastMap[$bylastday] ?? $bylastday;
        return str_replace(['[placeholder]','[placeholder_lastday]'], [$n, $lastStr],
            Text::_('com_planjeagenda_OUTPUT_LASTDAY'));
    }

    private static function formatLimitDate(string $date): string
    {
        try {
            return (new \DateTime($date))->format('d-m-Y');
        } catch (\Exception $e) {
            return $date;
        }
    }
}
