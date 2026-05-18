<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Model voor het ophalen van Events.
 * De naam moet exact overeenkomen met de PSR-4 standaard: EventsModel.
 */
class EventsModel extends ListModel
{
    /**
     * Constructor om filtervelden te definiëren.
     */
    public function __construct($config = [], $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'state', 'a.published',
                'dates', 
                'datestart','a.dates',
                'dateend','a.enddates',
                'times', 'a.times',
                'venue', 'loc.venue',
                'city', 'loc.city',
                'hits', 'a.hits',
                'featured', 'a.featured',
                'access', 'a.access', 'access_level',
                'access', 'a.access'
            );
        }

        parent::__construct($config, $factory);
    }

    /**
     * Zet de standaard filters en status van de lijst.
     */
    protected function populateState($ordering = 'a.dates', $direction = 'asc')
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
        $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
        $this->getUserStateFromRequest($this->context . '.filter.begin', 'filter_begin');
        $this->getUserStateFromRequest($this->context . '.filter.end', 'filter_end');
        $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->getUserStateFromRequest($this->context . '.filter.datestart', 'filter_datestart');
        $this->getUserStateFromRequest($this->context . '.filter.dateend', 'filter_dateend');

        // Laad parameters
        $params = ComponentHelper::getParams('com_planjeagenda');
        $this->setState('params', $params);

        // Paginering en sortering
        parent::populateState($ordering, $direction);
    }


    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.type');
        $id .= ':' . $this->getState('filter.begin');
        $id .= ':' . $this->getState('filter.end');
        $id .= ':' . serialize($this->getState('filter.access'));
        $id .= ':' . $this->getState('filter.datestart');
        $id .= ':' . $this->getState('filter.dateend');
        
        return parent::getStoreId($id);
    }
    
    
    /**
     * SQL Query bouwen voor de lijst.
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Basis event-velden
        $query->select($this->getState('list.select', 'a.*'));
        $query->from($db->quoteName('#__pja_events', 'a'));

        // Locatie
        $query->select($db->quoteName(['loc.venue', 'loc.city']));
        $query->select('loc.checked_out AS vchecked_out');
        $query->join('LEFT', $db->quoteName('#__pja_venues', 'loc') . ' ON loc.id = a.locid');
        

        // Auteur
        $query->select('u.name AS author, u.email AS email');
        $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');

        // Editor (checked-out door)
        $query->select('ue.name AS editor');
        $query->join('LEFT', $db->quoteName('#__users', 'ue') . ' ON ue.id = a.checked_out');

        // Toegangsniveau label
        $query->select('ag.title AS access_level');
        $query->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ag.id = a.access');

        // Registratie tellingen
        $query->select('SUM(CASE WHEN r.waiting = 0 AND r.status = 1 THEN r.places ELSE 0 END) AS regCount');
        $query->select('SUM(CASE WHEN r.waiting = 1 AND r.status = 1 THEN r.places ELSE 0 END) AS waiting');
        $query->join('LEFT', $db->quoteName('#__pja_register', 'r') . ' ON r.event = a.id');
        $query->select('a.reservedplaces AS reserved');

        // Single-category: join directly via a.catid
        $query->join(
            'LEFT',
            $db->quoteName('#__pja_categories', 'c') . ' ON c.id = a.catid'
        );

        $query->group(
            'a.id, loc.venue, loc.city, u.name, u.email, ue.name, ag.title'
        );

        // Filter: categorie
        $catId = $this->getState('filter.category_id');
        if (is_numeric($catId) && $catId > 0) {
            $query->where('a.catid = ' . (int) $catId);
        }

        // Filter: status (gepubliceerd)
        $state = $this->getState('filter.state');
        if ($state !== '' && $state !== null) {
            $query->where('a.published = ' . (int) $state);
        }

        // Filter: uitgelicht
        $featured = $this->getState('filter.featured');
        if (is_numeric($featured)) {
            $query->where('a.featured = ' . (int) $featured);
        }

        // Filter: begin- en einddatum
        $begin = $this->getState('filter.datestart');
        $end   = $this->getState('filter.dateend');
        if (!empty($begin)) {
            $query->where('a.dates >= ' . $db->quote($begin));
        }
        if (!empty($end)) {
            $query->where('a.dates <= ' . $db->quote($end));
        }

        // zoekfilter 
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where(
                '(a.title LIKE ' . $like .
                ' OR loc.venue LIKE ' . $like .
                ' OR loc.city LIKE ' . $like .
                ' OR c.catname LIKE ' . $like . ')'
            );
        }
        
        
        // Filter on the language.
/* @todo fix, add later
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' = :language')
            ->bind(':language', $language);
        }
        */
        
        
        // Filter by access level.
        $access = $this->getState('filter.access');
        
        if (is_numeric($access)) {
            $access = (int) $access;
            $query->where($db->quoteName('a.access') . ' = :access')
            ->bind(':access', $access, ParameterType::INTEGER);
        } elseif (\is_array($access)) {
            $access = ArrayHelper::toInteger($access);
            $query->whereIn($db->quoteName('a.access'), $access);
        }
        

        // Sortering
        $ordering  = $this->state->get('list.ordering',  'a.dates');
        $direction = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($ordering) . ' ' . $db->escape($direction));

        return $query;
    }

    /**
     * Items ophalen en extra data (zoals categorieën) toevoegen.
     */
    public function getItems()
    {
        $items = parent::getItems();

        if (!$items) {
            return $items;
        }

        // FIX 5: laad alle categorieën voor de huidige pagina in ÉÉN query
        // in plaats van een aparte query per event (N+1 probleem).
        $ids = array_map(static fn($item) => (int) $item->id, $items);

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('ae.id AS itemid, c.id, c.catname, c.color, c.alias AS catslug, c.catname AS path')
            ->from($db->quoteName('#__pja_events', 'ae'))
            ->join(
                'LEFT',
                $db->quoteName('#__pja_categories', 'c') . ' ON c.id = ae.catid'
            )
            ->whereIn('ae.id', $ids)
            ->order('ae.id');

        $db->setQuery($query);
        $catRows = $db->loadObjectList();

        // Indexeer per event-id
        $catMap = [];
        foreach ($catRows as $row) {
            $catMap[$row->id][] = $row;
        }

        foreach ($items as $item) {
            $item->categories = $catMap[$item->id] ?? [];
        }

        return $items;
    }

    /**
     * Categorieën ophalen voor één specifiek event.
     * Nog steeds bruikbaar vanuit andere plekken (bv. EventModel).
     */
    public function getCategories(int $id): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('ae.id AS itemid, c.id, c.catname, c.color, c.alias AS catslug, c.catname AS path')
            ->from($db->quoteName('#__pja_events', 'ae'))
            ->join(
                'LEFT',
                $db->quoteName('#__pja_categories', 'c') . ' ON c.id = ae.catid'
            )
            ->where('ae.id = ' . (int) $id)
            ->order('ae.id');

        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    
    
    
    public function getMunicipalities()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        
        // Haal de unieke gemeentes op die gekoppeld zijn aan actieve locaties
        $query->select('id AS value,title AS text')
        ->from($db->quoteName('#__pja_municipalities')) // Pas aan naar jouw exacte tabelnaam
        ->order($db->quoteName('title') . ' ASC');
        
        $db->setQuery($query);
        return $db->loadObjectList();
    }
       
    
    
}