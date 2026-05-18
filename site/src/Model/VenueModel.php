<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;
use KoelmanLabs\Component\Planjeagenda\Site\Model\EventModel;
use KoelmanLabs\Component\Planjeagenda\Site\Model\EventslistModel;

class VenueModel extends EventslistModel
{
    /**
     * Venue id
     *
     * @var int
     */
    protected $_id = null;


    public function __construct()
    {
        $app    = Factory::getApplication();
        $jinput = $app->input;
        $params = $app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams();

        # determing the id to load
        if ($jinput->get('id',null,'int')) {
            $id = $jinput->get('id',null,'int');
        } else {
            $id = $params->get('id');
        }
        $this->setId((int)$id);

        parent::__construct();
    }

    /**
     * Method to auto-populate the model state.
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app         = Factory::getApplication();
        $jemsettings = PlanjeagendaHelper::config();
        $params = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
        $jinput      = $app->input;
        $task        = $jinput->getCmd('task','');
        $itemid      = $jinput->getInt('id', 0) . ':' . $jinput->getInt('Itemid', 0);
        $user        = Factory::getApplication()->getIdentity();
        $format      = $jinput->getCmd('format',false);

        // List state information

        if (empty($format) || ($format == 'html')) {
            /* in J! 3.3.6 limitstart is removed from request - but we need it! */
            if ($app->input->getInt('limitstart', null) === null) {
                $app->setUserState('com_planjeagenda.venue.'.$itemid.'.limitstart', 0);
            }

            $limit = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.limit', 'limit', $jemsettings->display_num, 'int');
            $this->setState('list.limit', $limit);

            $limitstart = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.limitstart', 'limitstart', 0, 'int');
            // correct start value if required
            $limitstart = $limit ? (int)(floor($limitstart / $limit) * $limit) : 0;
            $this->setState('list.start', $limitstart);
        }

        # Search
        $search = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_search', 'filter_search', '', 'string');
        $this->setState('filter.filter_search', $search);

        $month = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_month', 'filter_month', '', 'string');
        $this->setState('filter.filter_month', $month);

        # FilterType
        $filtertype = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_type', 'filter_type', 0, 'int');
        $this->setState('filter.filter_type', $filtertype);

        # filter_order
        $orderCol = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_order', 'filter_order', 'a.dates', 'cmd');
        $this->setState('filter.filter_ordering', $orderCol);

        # filter_direction
        $listOrder = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');
        $this->setState('filter.filter_direction', $listOrder);

        # show open date events
        # (there is no menu item option yet so show all events)
        $this->setState('filter.opendates', 1);

        $defaultOrder = ($task == 'archive') ? 'DESC' : 'ASC';
        if ($orderCol == 'a.dates') {
            $orderby = array('a.dates ' . $listOrder, 'a.times ' . $listOrder, 'a.created ' . $listOrder);
        } else {
            $orderby = array($orderCol . ' ' . $listOrder,
                             'a.dates ' . $defaultOrder, 'a.times ' . $defaultOrder, 'a.created ' . $defaultOrder);
        }
        $this->setState('filter.orderby', $orderby);

        # params
        $this->setState('params', $params);

        # publish state
        $this->_populatePublishState($task);

        $this->setState('filter.groupby',array('a.id'));
    }

    /**
     * Method to get a list of events.
     */
    public function getItems()
    {
        $items = parent::getItems();
        /* no additional things to do yet - place holder */
        if ($items) {
            return $items;
        }

        return array();
    }

    /**
     * @return    JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $query = parent::getListQuery();

        // here we can extend the query of the Eventslist model
        $query->where('a.locid = '.(int)$this->_id);

        return $query;
    }

    /**
     * Method to set the venue id
     *
     * The venue-id can be set by a menu-parameter
     */
    public function setId($id)
    {
        // Set new venue ID and wipe data
        $this->_id   = $id;
        //$this->_data = null;
    }

    /**
     * set limit
     * @param int value
     */
    public function setLimit($value)
    {
        $this->setState('limit', (int) $value);
    }

    /**
     * set limitstart
     * @param int value
     */
    public function setLimitStart($value)
    {
        $this->setState('limitstart', (int) $value);
    }

    /**
     * Method to get a specific Venue
     *
     * @access public
     * @return array
     */
    public function getVenue()
    {
        $user   = Factory::getApplication()->getIdentity();
        $levels = $user->getAuthorisedViewLevels();
        $jemsettings = PlanjeagendaHelper::config();

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query  = $db->getQuery(true);

        $query->select('id, venue, published, city, state, url, street, custom1, custom2, custom3, custom4, custom5, '.
                       ' custom6, custom7, custom8, custom9, custom10, locimage, meta_keywords, meta_description, access, '.
                       ' created, created_by, locdescription, country, map, latitude, longitude, postalCode, checked_out AS vChecked_out, checked_out_time AS vChecked_out_time, '.
                       ' CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug');
        $query->from($db->quoteName('#__pja_venues'));

        $case_when_a  = ' CASE WHEN ';
        $case_when_a .= " access IN (" . implode(',',$levels) . ")";
        $case_when_a .= ' THEN 1 ';
        $case_when_a .= ' ELSE 0 ';
        $case_when_a .= ' END as user_has_access_venue';

        $query->select(array($case_when_a));

        # Filter by access level - public or with access_level_locked_venues active.
        if($jemsettings->access_level_locked_venues != "[\"1\"]") {
            $accessLevels = json_decode($jemsettings->access_level_locked_venues, true);
            $newlevels = array_values(array_unique(array_merge($levels, $accessLevels)));
            $query->where('access IN ('.implode(',', $newlevels).')');
        } else {
            $query->where('access IN ('.implode(',', $levels).')');
        }

        $query->where('id = '.(int)$this->_id);

        // all together: if published or the user is creator of the venue or allowed to edit or publish venues
        if (empty($user->id)) {
            $query->where('published = 1');
        }
        // no limit if user can publish or edit foreign venues
        elseif ($user->authorise('core.edit.state', 'com_planjeagenda')) {
            $query->where('published IN (0,1)');
        }
        // user maybe creator
        else {
            $query->where('(published = 1 OR (published = 0 AND created_by = ' . $this->_db->Quote($user->id) . '))');
        }

        $db->setQuery($query);
        $_venue = $db->loadObject();

        if (empty($_venue)) {
            Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_VENUE_ERROR_VENUE_NOT_FOUND'), 'error');
            return false;
        }else if(!$_venue->user_has_access_venue) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }

        $_venue->attachments = \PlanjeagendaAttachment::getAttachments('venue'.$_venue->id);

        return $_venue;
    }
}
