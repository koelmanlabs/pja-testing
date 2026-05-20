<?php
declare(strict_types=1);

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Repository;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class RegistrationRepository
{
    public function userAlreadyRegistered(
        int $eventId,
        int $userId,
        int $excludeId = 0
    ): bool {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from('#__pja_register')
            ->where('event = ' . (int) $eventId)
            ->where('uid = ' . (int) $userId);

        if ($excludeId > 0) {
            $query->where('id != ' . (int) $excludeId);
        }

        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    public function getEventRegistrationStats(int $eventId): object
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->select([
                'COUNT(id) AS registered',
                'COALESCE(SUM(waiting), 0) AS waiting'
            ])
            ->from('#__pja_register')
            ->where('status = 1')
            ->where('event = ' . (int) $eventId);

        $db->setQuery($query);

        $register = $db->loadObject();

        if (!$register) {
            $register = new \stdClass();
            $register->registered = 0;
            $register->waiting = 0;
        }

        $register->booked =
            (int) $register->registered +
            (int) $register->waiting;

        return $register;
    }
}