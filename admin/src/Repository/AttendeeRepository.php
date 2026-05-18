<?php 
namespace Planjeagenda\Component\Planjeagenda\Administrator\Repository;

use Joomla\CMS\Factory;

class AttendeeRepository
{
    public function exists(int $eventId, int $userId): bool
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from('#__pja_register')
            ->where('event = :eventId')
            ->where('uid = :userId')
            ->bind(':eventId', $eventId)
            ->bind(':userId', $userId);

        $db->setQuery($query);

        return (bool) $db->loadResult();
    }
}