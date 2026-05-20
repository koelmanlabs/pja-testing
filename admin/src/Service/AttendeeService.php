<?php
declare(strict_types=1);

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use KoelmanLabs\Component\Planjeagenda\Administrator\Repository\EventRepository;
use KoelmanLabs\Component\Planjeagenda\Administrator\Repository\RegistrationRepository;
use KoelmanLabs\Component\Planjeagenda\Administrator\Repository\AttendeeRepository;

class AttendeeService
{
    private EventRepository $events;
    private RegistrationRepository $registrations;
    private WaitingListService $waitingListService;
    private readonly AttendeeRepository $attendees;
    
    
    public function __construct()
    {
        $this->events = new EventRepository();
        $this->registrations = new RegistrationRepository();
        $this->waitingListService = new WaitingListService();
        $this->attendees = new AttendeeRepository();
    }
    
    public function toggle(object $attendee): bool
    {
        if (!$attendee->id) {
            throw new \RuntimeException(
                Text::_('COM_PLANJEAGENDA_MISSING_ATTENDEE_ID')
            );
        }

        $table = new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\jem_register(
            Factory::getContainer()->get('DatabaseDriver')
        );

        $table->bind($attendee);

        $table->waiting =
            ($attendee->waiting || $attendee->status == 2)
            ? 0
            : 1;

        if ($table->status == 2) {
            $table->status = 1;
        }

        return $table->store();
    }
    
    
    
    public function save(array $data): bool 
    { 
        $eventid = $data['event']; 
        $userid = $data['uid']; 
        $id = !empty($data['id']) ? (int) $data['id'] : 0; 
        $status = $data['status'] ?? false; 
        
        // Split status and waiting 
        if ($status !== false) { 
            if ($status == 2)  { 
                    $data['status'] = 1; 
                    $data['waiting'] = 1; 
                } elseif ($status == 1) { 
                    $data['waiting'] = 0; 
                } 
        } 
        
        $row = new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\jem_register( Factory::getContainer()->get('DatabaseDriver') ); 
        
        if ($id > 0) { 
            $row->load($id); 
        }
        
        if (!$row->bind($data)) {
            throw new \RuntimeException($row->getError());
        }
        
        $row->id = (int) $row->id;
        
        
        // Existing attendee update 
        if ($row->id > 0) { 
            if (!$row->check()) { 
                throw new \RuntimeException($row->getError()); 
            } if (!$row->store()) { 
                throw new \RuntimeException($row->getError()); 
            } return true; 
        } 
        
        // Prevent duplicate registration 
        if ( $this->registrations->userAlreadyRegistered( (int) $eventid, (int) $userid, (int) $row->id ) ) { 
            throw new \RuntimeException( Text::_('COM_PLANJEAGENDA_ERROR_USER_ALREADY_REGISTERED') ); 
        } 
        
        // Load event 
        $event = $this->events->findById((int) $eventid); 
        $events = [$event]; 
        
        // Series booking 
        if ( $event->recurrence_type && $event->seriesbooking && !empty($data['seriesbooking']) && empty($data['singlebooking']) ) { 
            $events = $this->events->getRecurringEvents($event); 
        }
        
        foreach ($events as $e) {
            
            // Skip duplicates 
            if ( $this->registrations->userAlreadyRegistered( (int) $e->id, (int) $userid ) ) { 
                continue; 
            } 
            
            $rowAux = clone $row; 
            $rowAux->event = $e->id; 
            $register = $this->registrations ->getEventRegistrationStats((int) $e->id); 
            $this->waitingListService->apply( $e, $register, $rowAux, (int) $status ); 
            
            if (!$rowAux->check()) { 
                throw new \RuntimeException($rowAux->getError()); 
            } if (!$rowAux->store()) { 
                throw new \RuntimeException($rowAux->getError()); 
            } 
        } return true; 
    }
    
    
    
    
    
    
    /**
     * Set attendee status
     */
    public function setStatus(array $pks, int $value = 1): bool
    {
        \Joomla\Utilities\ArrayHelper::toInteger($pks);
        
        if (empty($pks)) {
            throw new \RuntimeException(
                Text::_('JERROR_NO_ITEMS_SELECTED')
                );
        }
        
        // Split status and waiting
        if ($value === 2) {
            $status = 1;
            $waiting = 1;
        } else {
            $status = $value;
            $waiting = 0;
        }
        
        $db = Factory::getContainer()->get('DatabaseDriver');
        
        $query = $db->getQuery(true)
        ->update($db->quoteName('#__pja_register'))
        ->set([
            $db->quoteName('status') . ' = ' . (int) $status,
            $db->quoteName('waiting') . ' = ' . (int) $waiting,
        ])
        ->where(
            $db->quoteName('id')
            . ' IN (' . implode(',', $pks) . ')'
            );
        
        $db->setQuery($query);
        
        return (bool) $db->execute();
    }
    
    
    
    /*
     * 
     */
    public function createEmptyAttendee(int $eventId): object
    {
        $table = new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\jem_register(
            Factory::getContainer()->get('DatabaseDriver')
            );
        
        $table->username = null;
        
        $table->eventtitle = '';
        $table->event = 0;
        $table->maxbookeduser = 0;
        $table->minbookeduser = 0;
        $table->recurrence_type = '';
        $table->seriesbooking = 0;
        $table->waitinglist = 0;
        
        if ($eventId > 0) {
            
            $event = $this->events->findById($eventId);
            
            if ($event) {
                $table->eventtitle = $event->title;
                $table->event = $event->id;
                $table->maxbookeduser = $event->maxbookeduser;
                $table->minbookeduser = $event->minbookeduser;
                $table->recurrence_type = $event->recurrence_type;
                $table->seriesbooking = $event->seriesbooking;
                $table->waitinglist = $event->waitinglist ?? 0;
            }
        }
        
        return $table;
    }
    
    
    public function findById(int $id): ?object
    {
        return $this->attendees->findById($id);
    }
    
   
    
    
    
} // closing class