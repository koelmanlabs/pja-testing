<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;

class ExportModel extends BaseDatabaseModel
{
    /**
     * Haal gefilterde evenementen op voor export.
     */
    public function getEvents(array $options = []): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select([
            'e.id', 'e.title', 'e.alias', 'e.dates', 'e.enddates',
            'e.times', 'e.endtimes', 'e.published', 'e.created',
            'e.description', 'e.introtext', 'e.hits', 'e.featured',
            'e.recurrence_type', 'e.recurrence_number', 'e.recurrence_limit',
            'e.registra', 'e.maxplaces', 'e.waitinglist',
            'e.created_by_alias', 'e.created_by',
            'l.venue AS location_name', 'l.city', 'l.country',
        ])
        ->from('#__pja_events AS e')
        ->leftJoin('#__pja_venues AS l ON l.id = e.locid');

        // Filters
        if (!empty($options['date_from'])) {
            $query->where('e.dates >= ' . $db->quote($options['date_from']));
        }
        if (!empty($options['date_to'])) {
            $query->where('e.dates <= ' . $db->quote($options['date_to']));
        }
        if (isset($options['published']) && $options['published'] !== '') {
            $query->where('e.published = ' . (int)$options['published']);
        }
        if (!empty($options['catid'])) {
            $query->leftJoin('#__pja_cats_event_relations AS cer ON cer.itemid = e.id')
                  ->where('cer.catid = ' . (int)$options['catid']);
        }

        $query->order('e.dates ASC');

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    /**
     * Haal alle locaties op voor export.
     */
    public function getVenues(array $options = []): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select([
                'id', 'venue', 'alias', 'street', 'number', 'postalcode',
                'city', 'state', 'country', 'published', 'locdescription',
                'latitude', 'longitude', 'created', 'ordering',
            ])
            ->from('#__pja_venues');

        if (isset($options['published']) && $options['published'] !== '') {
            $query->where('published = ' . (int)$options['published']);
        }

        $query->order('venue ASC');

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    /**
     * Haal alle categorieën op voor export.
     */
    public function getCategories(array $options = []): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select([
                'id', 'catname', 'alias', 'description', 'published',
                'color', 'access', 'parent_id', 'level', 'lft', 'rgt',
            ])
            ->from('#__pja_categories')
            ->where('alias != ' . $db->quote('root'))
            ->order('lft ASC');

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    /**
     * Exporteer evenementen als CSV string.
     */
    public function toCsv(array $rows, string $separator = ';', string $delimiter = '"', bool $bom = true): string
    {
        if (empty($rows)) {
            return '';
        }

        ob_start();
        $handle = fopen('php://output', 'w');

        // BOM voor Excel UTF-8 herkenning
        if ($bom) {
            fputs($handle, "\xEF\xBB\xBF");
        }

        // Header rij
        fputcsv($handle, array_keys((array)$rows[0]), $separator, $delimiter);

        // Data rijen
        foreach ($rows as $row) {
            fputcsv($handle, (array)$row, $separator, $delimiter);
        }

        fclose($handle);
        return ob_get_clean();
    }

    /**
     * Exporteer als JSON (volledig met relaties).
     */
    public function toJson(array $options = []): string
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Haal categorie-evenement relaties op
        $relations = $db->setQuery(
            $db->getQuery(true)
                ->select(['itemid AS event_id', 'catid AS category_id'])
                ->from('#__pja_cats_event_relations')
        )->loadObjectList() ?: [];

        $data = [
            'export_info' => [
                'version'    => '1.0',
                'created_at' => date('Y-m-d H:i:s'),
                'component'  => 'com_planjeagenda',
                'generator'  => 'Plan Je Agenda by Koelman Labs',
            ],
            'events'      => $this->getEvents($options),
            'venues'      => $this->getVenues($options),
            'categories'  => $this->getCategories($options),
            'relations'   => $relations,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Exporteer als iCal (.ics) bestand.
     */
    public function toIcal(array $options = []): string
    {
        $events = $this->getEvents($options);

        $config = PlanjeagendaHelper::config();
        $siteName = \Joomla\CMS\Factory::getApplication()->get('sitename', 'Plan Je Agenda');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Koelman Labs//Plan Je Agenda//NL',
            'X-WR-CALNAME:' . $this->icalEscape($siteName),
            'X-WR-CALDESC:Geëxporteerd vanuit Plan Je Agenda',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
        ];

        foreach ($events as $event) {
            if (empty($event->dates)) {
                continue;
            }

            $dtstart = $this->formatIcalDate($event->dates, $event->times ?? '');
            $dtend   = $this->formatIcalDate(
                !empty($event->enddates) ? $event->enddates : $event->dates,
                !empty($event->endtimes) ? $event->endtimes : ($event->times ?? '')
            );

            $uid = 'event-' . $event->id . '@planjeagenda.koelmanlabs.nl';

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTART:' . $dtstart;
            $lines[] = 'DTEND:' . $dtend;
            $lines[] = 'SUMMARY:' . $this->icalEscape($event->title);
            $lines[] = 'DTSTAMP:' . date('Ymd\THis\Z');

            if (!empty($event->description)) {
                $desc = strip_tags($event->description);
                $lines[] = 'DESCRIPTION:' . $this->icalEscape($desc);
            }

            if (!empty($event->location_name)) {
                $location = $event->location_name;
                if (!empty($event->city)) {
                    $location .= ', ' . $event->city;
                }
                $lines[] = 'LOCATION:' . $this->icalEscape($location);
            }

            $lines[] = 'STATUS:' . ($event->published ? 'CONFIRMED' : 'TENTATIVE');
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        // iCal vereist CRLF regeleindes en max 75 tekens per regel
        return implode("\r\n", array_map([$this, 'icalFold'], $lines)) . "\r\n";
    }

    /**
     * Formatteer datum voor iCal.
     */
    private function formatIcalDate(string $date, string $time = ''): string
    {
        try {
            $dt = new DateTime($date . (!empty($time) ? ' ' . $time : ''));
            if (empty($time)) {
                return $dt->format('Ymd');
            }
            return $dt->format('Ymd\THis');
        } catch (\Exception $e) {
            return date('Ymd');
        }
    }

    /**
     * Escape iCal tekst waarden.
     */
    private function icalEscape(string $text): string
    {
        $text = str_replace(['\\', ';', ',', "\n", "\r"], ['\\\\', '\;', '\,', '\n', ''], $text);
        return $text;
    }

    /**
     * Vouw lange iCal regels op 75 tekens.
     */
    private function icalFold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $folded = '';
        while (strlen($line) > 75) {
            $folded .= substr($line, 0, 75) . "\r\n ";
            $line = substr($line, 75);
        }

        return $folded . $line;
    }

    /**
     * Haal beschikbare categorieën op voor filter dropdown.
     */
    public function getCategoryOptions(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select(['id', 'catname AS title'])
            ->from('#__pja_categories')
            ->where('published = 1')
            ->where('alias != ' . $db->quote('root'))
            ->order('catname ASC');

        return $db->setQuery($query)->loadObjectList() ?: [];
    }
}
