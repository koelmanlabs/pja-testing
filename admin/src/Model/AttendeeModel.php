<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
declare(strict_types=1);
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use KoelmanLabs\Component\Planjeagenda\Administrator\Service\AttendeeService;


class AttendeeModel extends AdminModel
{
    
    protected int $id = 0;
    protected ?object $data = null;
    private readonly AttendeeService $attendeeService;
    
    
    
    /**
     * Constructor
     */
    public function __construct($config = [], $factory = null)
    {
        parent::__construct($config);
        
        $this->attendeeService ??= new AttendeeService();
        
        $jinput = Factory::getApplication()->input;
        
        $array = $jinput->get('id', 0, 'array');
        
        if (is_array($array)) {
            $this->setId((int) $array[0]);
        } 
    }
    
    
    
    
    /*
     * 
     */
    public function getTable(
        $type = 'Attendee',
        $prefix = 'Table',
        $config = []
        ): object
    {
        return parent::getTable($type, $prefix, $config);
    }
    
    
    
    /*
     * 
     */
    protected function prepareTable($table): void
    {
        if (empty($table->id)) {
            $table->created = Factory::getDate()->toSql();
        }
        
        $table->modified = Factory::getDate()->toSql();
    }
    
    
    
    
    
    public function getForm($data = [], $loadData = true): \Joomla\CMS\Form\Form|false
    {
        return $this->loadForm(
            'com_planjeagenda.attendee',
            'attendee',
            [
                'control' => 'jform',
                'load_data' => $loadData,
            ]
            );
    }
    
    
    
    /*
     * 
     */
    protected function loadFormData(): ?object
    {
        return $this->getData();
    }
   
    
    
    /*
     * 
     */
    public function validate($form, $data, $group = null)
    {
        $validData = parent::validate($form, $data, $group);
        
        if ($validData === false) {
            return false;
        }
        
        if (empty($validData['event'])) {
            $this->setError(Text::_('COM_PLANJEAGENDA_ERROR_EVENT_REQUIRED'));
            return false;
        }
        
        if (empty($validData['uid'])) {
            $this->setError(Text::_('COM_PLANJEAGENDA_ERROR_USER_REQUIRED'));
            return false;
        }
        
        return $validData;
    }
    
   
 

    /**
     * Method to set the identifier
     *
     * @access public
     * @param  int  category identifier
     */
    public function setId(int $id): void
    {
        // Set category id and wipe data
        $this->id = (int) $id;
        $this->data = null;
    }

    
    
    /**
     * Method to get data
     *
     * @access public
     * @return array
     */
    public function getData(): object
    {
        if ($this->data !== null) {
            return $this->data;
        }
        
        if ($this->id > 0) {
            
            $this->data = $this->attendeeService
            ->findById($this->id);
            
            if ($this->data !== null) {
                return $this->data;
            }
        }
        
        $eventId = Factory::getApplication()
        ->input
        ->getInt('eventid', 0);
        
        $this->data = $this->attendeeService
        ->createEmptyAttendee($eventId);
        
        return $this->data;
    }
    
    
   
    /*
     * 
     */
    public function toggle(): bool
    {
        try {
            
            return $this->attendeeService
            ->toggle($this->getData());
            
        } catch (\RuntimeException $e) {
            
            $this->setError($e->getMessage());
            
            return false;
        }
    } 
    
    
    
    /**
     * Method to store the attendee
     */
    public function save($data): bool
    {
        try {
            
            return $this->attendeeService->save($data);
            
        } catch (\RuntimeException $e) {
            
            $this->setError($e->getMessage());
            
            Factory::getApplication()->enqueueMessage(
                $e->getMessage(),
                'warning'
                );
            
            return false;
        }
    }




    
    
public function setStatus(array $pks, int $value = 1): bool
{
    try {
        
        return $this->attendeeService
        ->setStatus($pks, $value);
        
    } catch (\RuntimeException $e) {
        
        $this->setError($e->getMessage());
        
        return false;
    }
}



} // closing class
