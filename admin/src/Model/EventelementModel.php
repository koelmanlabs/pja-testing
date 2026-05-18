<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Filter\InputFilter;

class EventelementModel extends BaseDatabaseModel
{
    /**
     * Events data array
     *
     * @var array
     */
    protected $_data = null;

    /**
     * Events total
     *
     * @var integer
     */
    protected $_total = null;

    /**
     * Pagination object
     *
     * @var object
     */
    protected $_pagination = null;

    /**
     * Constructor
     */
/**
     * Method to get categories item data
     *
     * @access public
     * @return array
     */
    public function getData()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_data))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

            if (is_array($this->_data)) {
                foreach ($this->_data as $item) {
                    $item->categories = $this->getCategories($item->id);

                    //remove events without categories (users have no access to them)
                    if (empty($item->categories)) {
                        unset($this->_data[$i]);
                    }
                }
            }
        }

        return $this->_data;
    }

    /**
     * Total nr of events
     *
     * @access public
     * @return integer
     */
    public function getTotal()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total))
        {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    /**
     * Method to get a pagination object for the events
     *
     * @access public
     * @return integer
     */
    public function getPagination()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination))
        {
            $this->_pagination = new Pagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }

        return $this->_pagination;
    }

    /**
     * Build the query
     *
     * @access protected
     * @return string
     */
    protected function _buildQuery()
    {
        // Get the WHERE and ORDER BY clauses for the query
        $where   = $this->_buildContentWhere();
        $orderby = $this->_buildContentOrderBy();

        $query = 'SELECT a.*, loc.venue, loc.city, c.catname'
               . ' FROM #__pja_events AS a'
               . ' LEFT JOIN #__pja_venues AS loc ON loc.id = a.locid'
               . ' LEFT JOIN #__pja_categories AS c ON c.id = a.catid'
               . $where
               . ' GROUP BY a.id'
               . $orderby
               ;

        return $query;
    }

    /**
     * Build the order clause
     *
     * @access protected
     * @return string
     */
    protected function _buildContentOrderBy()
    {
        $app = Factory::getApplication();

        $filter_order     = $app->getUserStateFromRequest( 'com_planjeagenda.eventelement.filter_order', 'filter_order', 'a.dates', 'cmd' );
        $filter_order_Dir = $app->getUserStateFromRequest( 'com_planjeagenda.eventelement.filter_order_Dir', 'filter_order_Dir', '', 'word' );

        $filter_order     = InputFilter::getInstance()->clean($filter_order, 'cmd');
        $filter_order_Dir = InputFilter::getInstance()->clean($filter_order_Dir, 'word');

        $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', a.dates';

        return $orderby;
    }

    /**
     * Build the where clause
     *
     * @access protected
     * @return string
     */
    protected function _buildContentWhere()
    {
        $app    = Factory::getApplication();
        $user   = Factory::getApplication()->getIdentity();
        $levels = $user->getAuthorisedViewLevels();
        $itemid = $app->input->getInt('id', 0) . ':' . $app->input->getInt('Itemid', 0);

        $published     = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_state',  'filter_state',  '', 'string');
        $filter_type   = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_type',   'filter_type',    0, 'int');
        $filter_search = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_search', 'filter_search', '', 'string');
        $filter_search = $this->_db->escape(trim(\Joomla\String\StringHelper::strtolower($filter_search)));

        $where = array();

        // Filter by published state
        if (is_numeric($published)) {
            $where[] = 'a.published = '.(int) $published;
        } elseif ($published === '') {
            $where[] = '(a.published IN (1))';
        }

        $where[] = ' c.published = 1';
        $where[] = ' c.access IN (' . implode(',', $levels) . ')';

        if (!empty($filter_search)) {
            switch ($filter_type) {
            case 1:
                $where[] = ' LOWER(a.title) LIKE \'%'.$filter_search.'%\' ';
                break;
            case 2:
                $where[] = ' LOWER(loc.venue) LIKE \'%'.$filter_search.'%\' ';
                break;
            case 3:
                $where[] = ' LOWER(loc.city) LIKE \'%'.$filter_search.'%\' ';
                break;
            case 4:
                $where[] = ' LOWER(c.catname) LIKE \'%'.$filter_search.'%\' ';
                break;
            }
        }

        $where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

        return $where;
    }

    public function getCategories($id)
    {
        // Ensure PlanjeagendaCategories class is loaded (defined in site/classes)
        if (!class_exists('PlanjeagendaCategories', false)) {
            $classFile = JPATH_SITE . '/components/com_planjeagenda/classes/categories.class.php';
            if (file_exists($classFile)) {
                require_once $classFile;
            }
        }

        $query = 'SELECT DISTINCT c.id, c.catname, c.checked_out AS cchecked_out'
               . ' FROM #__pja_categories AS c'
               . ' LEFT JOIN #__pja_events AS ae ON ae.catid = c.id'
               . ' WHERE ae.id = '.(int)$id
               ;

        $this->_db->setQuery( $query );
        $cats = $this->_db->loadObjectList();

        foreach ($cats as &$cat) {
            $jc = new \PlanjeagendaCategories($cat->id);
            $cat->parentcats = $jc->getParentlist();
        }

        return $cats;
    }
}
