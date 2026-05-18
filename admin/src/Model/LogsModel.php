<?php

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class LogsModel extends ListModel
{
    public function __construct($config = [], $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'level', 'label', 'created_at', 'memory_kb', 'time_ms'
            ];
        }
        parent::__construct($config, $factory);
    }
    
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        
        $query->select('*')
        ->from($db->quoteName('#__pja_debug_log'));
        
        // Filter op level (bijv. alleen errors tonen)
        $level = $this->getState('filter.level');
        if (!empty($level)) {
            $query->where($db->quoteName('level') . ' = ' . $db->quote($level));
        }
        
        // Sortering
        $orderCol  = $this->state->get('list.ordering', 'created_at');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        
        return $query;
    }
}