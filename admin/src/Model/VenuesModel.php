<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;

class VenuesModel extends ListModel
{
    public function __construct($config = [], $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'venue', 'a.venue',
                'alias', 'a.alias',
                'state', 'a.published', // Let op: veranderd van 'state' naar 'published'
                'country', 'a.country',
                'city', 'a.city',
                'ordering', 'a.ordering',
                'access', 'a.access',
                'created_by', 'a.created_by'
            );
        }

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'a.venue', $direction = 'asc')
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();
        
        $forcedLanguage = $input->get('forcedLanguage', '', 'cmd');
        
        // Adjust the context to support modal layouts.
        if ($layout = $input->get('layout')) {
            $this->context .= '.' . $layout;
        }
        
        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }
              
        $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
       
        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase(); // Directer dan getContainer
        $query = $db->getQuery(true);

        // Basis selectie
        $query->select(
            $this->getState(
                'list.select',
                'a.*' // Voor nu even simpel, je kunt later specifieke kolommen terugzetten
            )
        );
        $query->from($db->quoteName('#__pja_venues') . ' AS a');

        // Join over de gebruikers (Auteur)
        $query->select($db->quoteName('u.name', 'author'));
        $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');
        
        // Selecteer de naam van de gebruiker die het item heeft uitgecheckt
        $query->select($db->quoteName('uc.name', 'editor'))
        ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        // Join over de toegewezen events (Count)
        $query->select('COUNT(e.id) AS assignedevents'); // Let op: kolomnaam in events tabel checken
        $query->join('LEFT OUTER', $db->quoteName('#__pja_events', 'e') . ' ON e.locid = a.id');
        $query->group('a.id');
        
        
        // Join over de viewlevels
        $query->select($db->quoteName('ag.title', 'access_level'));
        $query->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ag.id = a.access');

        // Filter: Zoeken
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.venue LIKE ' . $search . ' OR a.city LIKE ' . $search . ')');
        }

        // Filter: Status (Gepubliceerd/Gearchiveerd)
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.published = ' . (int) $published);
        }
 
        /* @todo: fix later | Filter on the language.
         /*
         if ($language = $this->getState('filter.language')) {
         $query->where($db->quoteName('a.language') . ' = :language')
         ->bind(':language', $language);
         }
         */
        

        // Sortering (De veilige Joomla manier)
        $orderCol  = $this->state->get('list.ordering', 'a.venue');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
    
    
    
    
    
    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param  string  $id  A prefix for the store id.
     *
     * @return string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.language');
        
        return parent::getStoreId($id);
    }
    
    
    
    
    
    
    
    
}