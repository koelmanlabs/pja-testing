<?php

namespace Planjeagenda\Component\Planjeagenda\Administrator\Service;

use Joomla\CMS\Factory;
use Planjeagenda\Component\Planjeagenda\Administrator\Enum\AttendeeStatus;

class AttendeeService
{
    public function register(array $data): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // capacity check
        // waiting list check
        // validation
        // status assignment

        $table = $this->getTable();

        if (!$table->bind($data)) {
            throw new \RuntimeException($table->getError());
        }

        $table->status = AttendeeStatus::CONFIRMED;

        if (!$table->store()) {
            throw new \RuntimeException($table->getError());
        }

        return (int) $table->id;
    }
}