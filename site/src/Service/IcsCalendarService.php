<?php

namespace KoelmanLabs\Component\Planjeagenda\Site\Service;

defined('_JEXEC') or die;

use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Vevent;
use Kigkonsult\Icalcreator\Pc;
use DateTime;
use Exception;

class IcsCalendarService
{
    /**
     * Build ICS calendar string from event items
     *
     * @param array $items
     * @return string
     */
    public function build(array $items): string
    {
        require_once __DIR__ . '/../Helper/icalcreator/autoload.php';
        $calendar = Vcalendar::factory([
            Vcalendar::UNIQUE_ID => 'planjeagenda.local',
        ]);

        // Calendar meta (safe X-props)
        $calendar->setXprop('X-WR-CALNAME', 'Planjeagenda');
        $calendar->setXprop('X-WR-CALDESC', 'Evenementen overzicht');
        $calendar->setXprop('X-WR-TIMEZONE', 'Europe/Amsterdam');

        foreach ($items as $item) {

            if (empty($item->id)) {
                continue;
            }

            $event = $calendar->newVevent();

            // -------------------------
            // TITLE / SUMMARY
            // -------------------------
            $event->setSummary($this->escape($item->title ?? 'Untitled'));

            // -------------------------
            // DTSTART NORMALIZATION
            // -------------------------
            $start = $this->toDateTime($item->dates ?? null);
            if (!$start) {
                continue; // skip invalid events
            }

            // Detect all-day event
            $isAllDay = $this->isAllDay($item->dates);

            if ($isAllDay) {
                $event->setDtstart($start, ['VALUE' => 'DATE']);
            } else {
                $event->setDtstart($start);
            }

            // -------------------------
            // DTEND NORMALIZATION (FIX CRASH)
            // -------------------------
            $end = $this->toDateTime($item->enddates ?? null);

            if ($end) {

                // FIX: ensure DTEND >= DTSTART
                if ($end < $start) {
                    $end = clone $start;
                }

                if ($isAllDay) {
                    $event->setDtend($end, ['VALUE' => 'DATE']);
                } else {
                    $event->setDtend($end);
                }
            }

            // -------------------------
            // UID
            // -------------------------
            $event->setUid('event-' . $item->id . '@planjeagenda');

            // -------------------------
            // ORGANIZER FIX (your major bug)
            // -------------------------
            $event->setOrganizer(
                $this->formatOrganizer($item->author ?? null)
            );

            // -------------------------
            // LOCATION
            // -------------------------
            if (!empty($item->venue)) {
                $event->setLocation($item->venue);
            }

            // -------------------------
            // STATUS
            // -------------------------
            $event->setStatus('CONFIRMED');
        }

        return $calendar->createCalendar();
    }

    /**
     * Convert DB date string to DateTime safely
     */
    private function toDateTime(?string $value): ?DateTime
    {
        if (empty($value)) {
            return null;
        }

        try {
            return new DateTime($value);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Detect all-day events
     * (based on VALUE=DATE or missing time part)
     */
    private function isAllDay(?string $value): bool
    {
        if (empty($value)) {
            return true;
        }

        // YYYY-MM-DD only => all day
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    /**
     * FIX: organizer must be Pc|string|null, NOT array
     */
    private function formatOrganizer(?string $author): ?string
    {
        if (empty($author)) {
            return null;
        }

        return 'CN=' . $author . ':MAILTO:no-reply@localhost';
    }
    
    
    
    
    private function escape(string $text): string
    {
        return addcslashes($text, ",;\\");
    }
    
    
}