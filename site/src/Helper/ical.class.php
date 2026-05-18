<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * iCalendar wrapper for kigkonsult/iCalcreator v2.41.92.
 *
 * Replaces the bundled iCalcreator v2.20 (2014, EOL) with the current
 * maintained library. All JEM calling code continues to use the public
 * methods of this class unchanged.
 *
 * Migration summary from v2.20:
 *  - vcalendar / vevent global classes  → Kigkonsult\Icalcreator\Vcalendar / Vevent
 *  - Vcalendar::factory([config])       instead of new vcalendar()
 *  - $vcal->newVevent()                 instead of new vevent() + addComponent()
 *  - setSummary(), setDtstart(), etc.   instead of setProperty('summary', ...)
 *  - new DateTime(...)                  for all date values (not associative arrays)
 *  - $vcal->returnCalendar(...)         unchanged, but filename is now passed directly
 *  - setXprop(X_WR_TIMEZONE, ...)       instead of setProperty('X-WR-TIMEZONE', ...)
 *  - Library internally handles all RFC 5545 text escaping
 */

defined('_JEXEC') or die;

// Load the new iCalcreator autoloader
require_once __DIR__ . '/icalcreator/autoload.php';

use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\IcalInterface;

class PlanjeagendaIcal
{
    /** @var Vcalendar */
    private Vcalendar $vcal;

    /** @var string  Safe filename for Content-Disposition */
    private string $filename = 'calendar.ics';

    public function __construct()
    {
        $this->vcal = Vcalendar::factory([
            Vcalendar::UNIQUE_ID => 'klevents.koelmanlabs.nl',
        ]);
    }

    /**
     * Sanitise a string for safe use as an HTTP Content-Disposition filename.
     * Strips everything except alphanumerics, dash, underscore, dot.
     */
    public static function safeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $name);
        if (!str_ends_with(strtolower($name), '.ics')) {
            $name .= '.ics';
        }
        return $name;
    }

    /**
     * Set calendar-level configuration.
     *
     * Supported keys: 'filename', 'unique_id'
     * All others are passed through to the underlying Vcalendar.
     */
    public function setConfig(string $key, string $value): void
    {
        if (strtolower($key) === 'filename') {
            $this->filename = self::safeFilename($value);
        } elseif (strtolower($key) === 'unique_id') {
            // Passed at construction — ignore silently (already default)
        }
        // 'directory' from old v2.20 API - no longer needed, ignore
    }

    /**
     * Set a top-level calendar property.
     *
     * Maps old v2.20 setProperty() calls to the typed v2.41 setters.
     */
    public function setProperty(string $name, $value, array $params = []): void
    {
        switch (strtolower($name)) {
            case 'calscale':
                $this->vcal->setCalscale((string)$value);
                break;
            case 'method':
                $this->vcal->setMethod((string)$value);
                break;
            case 'x-wr-timezone':
                $this->vcal->setXprop(Vcalendar::X_WR_TIMEZONE, (string)$value);
                break;
            case 'x-wr-calname':
                $this->vcal->setXprop(Vcalendar::X_WR_CALNAME, (string)$value);
                break;
            default:
                // Generic X- property fallback
                $this->vcal->setXprop(strtoupper($name), (string)$value);
                break;
        }
    }

    /**
     * Add a VEVENT to the calendar.
     *
     * Accepts an array of property definitions keyed by property name.
     * Each entry is either:
     *   - ['value' => $v, 'params' => $p]  (full form)
     *   - a scalar value                    (shorthand, no params)
     * Null entries are silently skipped (optional properties).
     *
     * Supported properties: summary, categories, dtstart, dtend,
     *                       description, location, url, uid
     *
     * Date values for dtstart/dtend must be associative arrays built by
     * JEM's icalAddEvent() in the form:
     *   ['year'=>Y, 'month'=>M, 'day'=>D]               (date-only)
     *   ['year'=>Y, 'month'=>M, 'day'=>D, 'hour'=>H, 'min'=>m, 'sec'=>s]  (datetime)
     * with params like ['VALUE'=>'DATE'] or ['VALUE'=>'DATE-TIME', 'TZID'=>'...']
     */
    public function addEvent(array $props): void
    {
        $vevent = $this->vcal->newVevent();

        foreach (array_filter($props, fn($v) => $v !== null) as $name => $def) {
            $value  = is_array($def) && isset($def['value'])  ? $def['value']  : $def;
            $params = is_array($def) && isset($def['params']) ? $def['params'] : [];

            switch (strtolower($name)) {
                case 'summary':
                    $vevent->setSummary((string)$value);
                    break;

                case 'categories':
                    // v2.41 setCategories accepts a string or array
                    $vevent->setCategories((string)$value);
                    break;

                case 'description':
                    $vevent->setDescription((string)$value);
                    break;

                case 'location':
                    $vevent->setLocation((string)$value);
                    break;

                case 'url':
                    try {
                        $vevent->setUrl((string)$value);
                    } catch (\Exception $e) {
                        // Invalid URL - skip rather than crash
                    }
                    break;

                case 'uid':
                    $vevent->setUid((string)$value);
                    break;

                case 'dtstart':
                    $dt = self::buildDateTime($value, $params);
                    if ($dt !== null) {
                        $vevent->setDtstart($dt, self::tzParam($params));
                    }
                    break;

                case 'dtend':
                    $dt = self::buildDateTime($value, $params);
                    if ($dt !== null) {
                        $vevent->setDtend($dt, self::tzParam($params));
                    }
                    break;
            }
        }
    }

    /**
     * Convert a JEM date array (old v2.20 format) to a DateTimeImmutable.
     *
     * @param array|string $value  Date value - associative array or ISO string
     * @param array        $params Property params (used to detect date-only vs datetime)
     * @return \DateTimeImmutable|null
     */
    private static function buildDateTime($value, array $params): ?\DateTimeImmutable
    {
        try {
            if (is_string($value)) {
                return new \DateTimeImmutable($value);
            }

            if (!is_array($value) || empty($value['year'])) {
                return null;
            }

            $isDateOnly = (($params['VALUE'] ?? '') === 'DATE');
            $tzid       = $params['TZID'] ?? null;

            $y  = (int)$value['year'];
            $m  = (int)($value['month'] ?? 1);
            $d  = (int)($value['day']   ?? 1);
            $H  = (int)($value['hour']  ?? 0);
            $i  = (int)($value['min']   ?? 0);
            $s  = (int)($value['sec']   ?? 0);

            $str = sprintf('%04d-%02d-%02d', $y, $m, $d);
            if (!$isDateOnly) {
                $str .= sprintf('T%02d:%02d:%02d', $H, $i, $s);
            }

            $tz = $tzid ? new \DateTimeZone($tzid) : null;
            return $tz
                ? new \DateTimeImmutable($str, $tz)
                : new \DateTimeImmutable($str);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract TZID param for the new setDtstart/setDtend API.
     * v2.41 reads TZID from the DateTime timezone automatically,
     * so we only need to signal VALUE=DATE for date-only events.
     */
    private static function tzParam(array $params): array
    {
        $out = [];
        if (($params['VALUE'] ?? '') === 'DATE') {
            $out[IcalInterface::VALUE] = IcalInterface::DATE;
        }
        return $out;
    }

    /**
     * Send the iCal file to the browser with a sanitised filename.
     */
    public function send(): void
    {
        PlanjeagendaDebug::log('iCal send', 'filename=' . $this->filename);
        $this->vcal->returnCalendar(false, false, true, $this->filename);
    }
}
