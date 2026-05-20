<?php
declare(strict_types=1);

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Repository;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class AttendeeRepository
{
    public function findById(int $id): ?object
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $query->select([
            'r.*',
            'u.name AS username',
            'a.title AS eventtitle',
            'a.waitinglist',
            'a.maxbookeduser',
            'a.minbookeduser',
            'a.recurrence_type',
            'a.seriesbooking'
        ]);

        $query->from('#__pja_register AS r');

        $query->join(
            'LEFT',
            '#__users AS u ON u.id = r.uid'
        );

        $query->join(
            'LEFT',
            '#__pja_events AS a ON a.id = r.event'
        );

        $query->where('r.id = ' . (int) $id);

        $db->setQuery($query);

        $item = $db->loadObject();

        if (
            $item
            && !empty($item->waiting)
            && (int) $item->status === 1
        ) {
            $item->status = 2;
        }

        return $item;
    }
}