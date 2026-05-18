<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;
use KoelmanLabs\Component\Planjeagenda\Site\Model\EventModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

class EditeventModel extends EventModel
{
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load state from the request.
        $pk = $app->input->getInt('a_id', 0);
        $this->setState('event.id', $pk);

        $fromId = $app->input->getInt('from_id', 0);
        $this->setState('event.from_id', $fromId);

        $catid = $app->input->getInt('catid', 0);
        $this->setState('event.catid', $catid);

        $locid = $app->input->getInt('locid', 0);
        $this->setState('event.locid', $locid);

        $date = $app->input->getCmd('date', '');
        $this->setState('event.date', $date);

        $return = $app->input->get('return', '', 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = $app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', $app->input->getCmd('layout', ''));
    }

    /**
     * Method to get event data.
     *
     * @param  integer The id of the event.
     *
     * @return mixed item data object on success, false on failure.
     */
    public function getItem($itemId = null)
    {
        $jemsettings = PlanjeagendaHelper::config();

        // Initialise variables.
        $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('event.id');

        $doCopy = false;
        if (!$itemId && $this->getState('event.from_id')) {
            $itemId = $this->getState('event.from_id');
            $doCopy = true;
        }

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($itemId);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }

        $properties = $table->getProperties(1);
        $value = ArrayHelper::toObject($properties, 'stdClass');

        if ($doCopy) {
            $value->id = 0;
            $value->author_ip = '';
            $value->created = '';
            $value->created_by = '';
            $value->created_by_alias = '';
            $value->modified = '';
            $value->modified_by = '';
            $value->version = '';
            $value->hits = '';
            $value->recurrence_type = 0;
            $value->recurrence_first_id = 0;
            $value->recurrence_counter = 0;
        }

        // Backup current recurrence values
        if ($value->id) {
            $value->recurr_bak = new stdClass;
            foreach (get_object_vars($value) as $k => $v) {
                if (strncmp('recurrence_', $k, 11) === 0) {
                    $value->recurr_bak->$k = $v;
                }
            }
        }

        // Convert attrib field to Registry.
        $registry = new \Registry();
        $registry->loadString($value->attribs ?? '{}');

        $globalregistry = PlanjeagendaHelper::globalattribs();

        $value->params = clone $globalregistry;
        $value->params->merge($registry);

        // Compute selected asset permissions.
        $user = Factory::getApplication()->getIdentity();
        //$userId = $user->id;
        //$asset = 'com_planjeagenda.event.' . $value->id;
        //$asset = 'com_planjeagenda';

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(array('count(id)'));
        $query->from('#__pja_register');
        $query->where(array('event = ' . $db->quote($value->id), 'waiting = 0', 'status = 1'));

        $db->setQuery($query);
        $res = $db->loadResult();
        $value->booked = (int)$res;
        if (!empty($value->maxplaces)) {
            $value->avplaces = $value->maxplaces - $value->booked;
        }

        // Get attachments - but not on copied events
        $files = \PlanjeagendaAttachment::getAttachments('event' . $value->id);
        $value->attachments = $files;

        // Preset values on new events
        if (!$itemId) {
            $catid = (int) $this->getState('event.catid');
            $locid = (int) $this->getState('event.locid');
            $date  = $this->getState('event.date');

            // ???
            if (empty($value->catid) && !empty($catid)) {
                $value->catid = $catid;
            }

            if (empty($value->locid) && !empty($locid)) {
                $value->locid = $locid;
            }

            if (empty($value->dates) && PlanjeagendaHelper::isValidDate($date)) {
                $value->dates = $date;
            }
        }

        // Check edit permission using component settings (not just Joomla core ACL)
        $jemsettings = PlanjeagendaHelper::config();
        $eventedit   = isset($jemsettings->eventedit) ? (int)$jemsettings->eventedit : 0;
        $eventowner  = isset($jemsettings->eventowner) ? (int)$jemsettings->eventowner : 0;
        $canEdit     = $user->authorise('core.edit', 'com_planjeagenda')
                    || ($eventedit == -1 && $user->id)
                    || ($eventowner && $user->id && $user->id == ($value->created_by ?? 0));
        $value->params->set('access-edit', $canEdit);

        // Check edit state permission.
        if (!$itemId && ($catId = (int) $this->getState('event.catid'))) {
            // New item.
            $cats = array($catId);
        } else {
            // Existing item (or no category)
            $cats = false;
        }
        $value->params->set('access-change', $user->authorise('core.edit.state', 'com_planjeagenda'));

        $value->author_ip = $jemsettings->storeip ? PlanjeagendaHelper::retrieveIP() : false;

        $value->articletext = $value->introtext;
        if (!empty($value->fulltext)) {
            $value->articletext .= '<hr id="system-readmore" />' . $value->fulltext;
        }

        return $value;
    }

    protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
    {
    //    JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');

        return parent::loadForm($name, $source, $options, $clear, $xpath);
    }

    /**
     * Get the return URL.
     *
     * @return string return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }

    ############
    ## VENUES ##
    ############

    /**
     * Get venues-data
     */
    public function getVenues()
    {
        $query      = $this->buildQueryVenues();
        $pagination = $this->getVenuesPagination();

        $rows = $this->_getList($query, $pagination->limitstart, $pagination->limit);

        return $rows;
    }

    /**
     * venues-query
     */
    protected function buildQueryVenues()
    {
        $app              = Factory::getApplication();
        $params           = PlanjeagendaHelper::globalattribs();

        $filter_order     = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_order', 'filter_order', 'l.venue', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');

        $allowedVenueCols = ['l.venue', 'l.city', 'l.state', 'l.country', 'l.ordering', 'l.id'];
        $filter_order     = PlanjeagendaHelper::sanitizeOrderCol((string)$filter_order, $allowedVenueCols, 'l.venue');
        $filter_order_Dir = PlanjeagendaHelper::sanitizeOrderDir((string)$filter_order_Dir);

        $filter_type      = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_type', 'filter_type', 0, 'int');
        $search           = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_search', 'filter_search', '', 'string');

        // Query - db must be defined before escape() call
        $db = Factory::getContainer()->get('DatabaseDriver');
        $search           = $db->escape(trim(\Joomla\String\StringHelper::strtolower($search)));
        $query = $db->getQuery(true);
        $query->select(array('l.id','l.state','l.city','l.country','l.published','l.venue','l.ordering'));
        $query->from('#__pja_venues as l');

        // where
        $where = array();
        $where[] = 'l.published = 1';

        /* something to search for? (we like to search for "0" too) */
        if ($search || ($search === "0")) {
            switch ($filter_type) {
                case 1: /* Search venues */
                    $where[] = 'LOWER(l.venue) LIKE ' . $db->quote('%' . $search . '%');
                    break;
                case 2: // Search city
                    $where[] = 'LOWER(l.city) LIKE ' . $db->quote('%' . $search . '%');
                    break;
                case 3: // Search state
                    $where[] = 'LOWER(l.state) LIKE ' . $db->quote('%' . $search . '%');
            }
        }

        if ($params->get('global_show_ownedvenuesonly')) {
            $user = Factory::getApplication()->getIdentity();
            $userid = $user->id;
            $where[] = ' created_by = ' . (int) $userid;
        }

        $query->where($where);

        // ordering - whitelisted
        $query->order($filter_order . ' ' . $filter_order_Dir);

        return $query;
    }

    /**
     * venues-Pagination
     **/
    public function getVenuesPagination()
    {
        $jemsettings = PlanjeagendaHelper::config();
        $app         = Factory::getApplication();
        $limit       = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.limit', 'limit', $jemsettings->display_num, 'int');
        $limitstart  = $app->input->getInt('limitstart', 0);
        // correct start value if required
        $limitstart  = $limit ? (int)(floor($limitstart / $limit) * $limit) : 0;

        $query = $this->buildQueryVenues();
        $total = $this->_getListCount($query);

        // Create the pagination object
        $pagination = new Pagination($total, $limitstart, $limit);

        return $pagination;
    }

    ##############
    ## CONTACTS ##
    ##############

    /**
     * Get contacts-data
     */
    public function getContacts()
    {
        $query      = $this->buildQueryContacts();
        $pagination = $this->getContactsPagination();

        $rows = $this->_getList($query, $pagination->limitstart, $pagination->limit);

        return $rows;
    }

    /**
     * contacts-Pagination
     **/
    public function getContactsPagination()
    {
        $jemsettings = PlanjeagendaHelper::config();
        $app         = Factory::getApplication();
        $limit       = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.limit', 'limit', $jemsettings->display_num, 'int');
        $limitstart  = $app->input->getInt('limitstart', 0);
        // correct start value if required
        $limitstart  = $limit ? (int)(floor($limitstart / $limit) * $limit) : 0;

        $query = $this->buildQueryContacts();
        $total = $this->_getListCount($query);

        // Create the pagination object
        $pagination = new Pagination($total, $limitstart, $limit);

        return $pagination;
    }

    /**
     * contacts-query
     */
    protected function buildQueryContacts()
    {
        $app              = Factory::getApplication();
        $jemsettings      = PlanjeagendaHelper::config();

        $filter_order     = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.filter_order', 'filter_order', 'con.ordering', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.filter_order_Dir', 'filter_order_Dir', '', 'word');

        $filter_order     = InputFilter::getinstance()->clean($filter_order, 'cmd');
        $filter_order_Dir = InputFilter::getinstance()->clean($filter_order_Dir, 'word');

        $filter_type      = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.filter_type', 'filter_type', 0, 'int');
        $search           = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.filter_search', 'filter_search', '', 'string');
        $search           = $this->_db->escape(trim(\Joomla\String\StringHelper::strtolower($search)));

        // Query
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(array('con.*'));
        $query->from('#__contact_details As con');

        // where
        $where = array();
        $where[] = 'con.published = 1';

        /* something to search for? (we like to search for "0" too) */
        if ($search || ($search === "0")) {
            switch ($filter_type) {
                case 1: /* Search name */
                    $where[] = ' LOWER(con.name) LIKE \'%' . $search . '%\' ';
                    break;
                case 2: /* Search address (not supported yet, privacy) */
                    //$where[] = ' LOWER(con.address) LIKE \'%' . $search . '%\' ';
                    break;
                case 3: // Search city
                    $where[] = ' LOWER(con.suburb) LIKE \'%' . $search . '%\' ';
                    break;
                case 4: // Search state
                    $where[] = ' LOWER(con.state) LIKE \'%' . $search . '%\' ';
                    break;
            }
        }
        $query->where($where);

        // ordering

        // ensure it's a valid order direction (asc, desc or empty)
        if (!empty($filter_order_Dir) && strtoupper($filter_order_Dir) !== 'DESC') {
            $filter_order_Dir = 'ASC';
        }

        if ($filter_order != '') {
            $orderby = $filter_order . ' ' . $filter_order_Dir;
            if ($filter_order != 'con.name') {
                $orderby = array($orderby, 'con.name'); // in case of city or state we should have a useful second ordering
            }
        } else {
            $orderby = 'con.name';
        }
        $query->order($orderby);

        return $query;
    }

    ###########
    ## USERS ##
    ###########

    /**
     * Get users data
     */
    public function getUsers()
    {
        $query      = $this->buildQueryUsers();
        $pagination = $this->getUsersPagination();

        $rows       = $this->_getList($query, $pagination->limitstart, $pagination->limit);

        // Add registration status if available
        $itemId     = (int)$this->getState('event.id');
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $qry        = $db->getQuery(true);
        // #__pja_register (id, event, uid, waiting, status, comment)
        $qry->select(array('reg.uid, reg.status, reg.waiting, reg.places'));
        $qry->from('#__pja_register As reg');
        $qry->where('reg.event = ' . $itemId);
        $db->setQuery($qry);
        $regs = $db->loadObjectList('uid');

    //    PlanjeagendaHelper::addLogEntry((string)$qry . "\n" . print_r($regs, true), __METHOD__);

        foreach ($rows AS &$row) {
            if (array_key_exists($row->id, $regs)) {
                $row->status = $regs[$row->id]->status;
                $row->places = $regs[$row->id]->places;
                if ($row->status == 1 && $regs[$row->id]->waiting) {
                    ++$row->status;
                }
            } else {
                $row->status = -99;
                $row->places = 0;
            }
        }

        return $rows;
    }

    /**
     * users-Pagination
     **/
    public function getUsersPagination()
    {
        $jemsettings = PlanjeagendaHelper::config();
        $app         = Factory::getApplication();
        $limit       = 0;//$app->getUserStateFromRequest('com_planjeagenda.selectusers.limit', 'limit', $jemsettings->display_num, 'int');
        $limitstart  = 0;//$app->input->getInt('limitstart', 0);
        // correct start value if required
        $limitstart  = $limit ? (int)(floor($limitstart / $limit) * $limit) : 0;

        $query = $this->buildQueryUsers();
        $total = $this->_getListCount($query);

        // Create the pagination object
        $pagination = new Pagination($total, $limitstart, $limit);

        return $pagination;
    }

    /**
     * users-query
     */
    protected function buildQueryUsers()
    {
        $app              = Factory::getApplication();
        $jemsettings      = PlanjeagendaHelper::config();

        // no filters, hard-coded
        $filter_order     = 'usr.name';
        $filter_order_Dir = '';
        $filter_type      = '';
        $search           = '';

        // Query
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(array('usr.id, usr.name'));
        $query->from('#__users As usr');

        // where
        $where = array();
        $where[] = 'usr.block = 0';
        $where[] = 'NOT usr.activation > 0';

        /* something to search for? (we like to search for "0" too) */
        if ($search || ($search === "0")) {
            switch ($filter_type) {
                case 1: /* Search name */
                    $where[] = ' LOWER(usr.name) LIKE \'%' . $search . '%\' ';
                    break;
            }
        }
        $query->where($where);

        // ordering

        // ensure it's a valid order direction (asc, desc or empty)
        if (!empty($filter_order_Dir) && strtoupper($filter_order_Dir) !== 'DESC') {
            $filter_order_Dir = 'ASC';
        }

        if ($filter_order != '') {
            $orderby = $filter_order . ' ' . $filter_order_Dir;
            if ($filter_order != 'usr.name') {
                $orderby = array($orderby, 'usr.name'); // in case of (???) we should have a useful second ordering
            }
        } else {
            $orderby = 'usr.name ' . $filter_order_Dir;
        }
        $query->order($orderby);

        return $query;
    }

    /**
     * Get list of invited users.
     */
    public function getInvitedUsers()
    {
        $itemId = (int)$this->getState('event.id');
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        // #__pja_register (id, event, uid, waiting, status, comment)
        $query->select(array('reg.uid'));
        $query->from('#__pja_register As reg');
        $query->where('reg.event = ' . $itemId);
        $query->where('reg.status = 0');
        $db->setQuery($query);
        $regs = $db->loadColumn();

    //    PlanjeagendaHelper::addLogEntry((string)$query . "\n" . implode(',', $regs), __METHOD__);
        return $regs;
    }

}
