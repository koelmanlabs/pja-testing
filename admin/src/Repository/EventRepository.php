<?php
declare(strict_types=1);

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Repository;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class EventRepository
{
    public function findById(int $eventId): ?object
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__pja_events')
            ->where('id = ' . (int) $eventId);

        $db->setQuery($query);

        return $db->loadObject();
    }

    public function getRecurringEvents(object $event): array
    {
        if (!$event->recurrence_type) {
            return [$event];
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        $seriesId = $event->recurrence_first_id ?: $event->id;

        $dateFrom = date('Y-m-d');
        $timeFrom = date('H:i:s');

        $query = $db->getQuery(true);

        $query->select('*')
            ->from('#__pja_events AS a')
            ->where(
                '(
                    (a.recurrence_first_id = 0 AND a.id = ' . (int) $seriesId . ')
                    OR a.recurrence_first_id = ' . (int) $seriesId . '
                )'
            )
            ->where(
                '(' .
                $db->quoteName('a.dates') . ' > ' . $db->quote($dateFrom) .
                ' OR (' .
                $db->quoteName('a.dates') . ' = ' . $db->quote($dateFrom) .
                ' AND ' .
                $db->quoteName('a.times') . ' >= ' . $db->quote($timeFrom) .
                '))'
            );

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}