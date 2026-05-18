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
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Filter\InputFilter;

class UserelementModel extends BaseDatabaseModel
{
    /**
     * data array
     *
     * @var array
     */
    protected $_data = null;

    /**
     * total
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
     *
     */
/**
     * Method to get data
     *
     * @access public
     * @return array
     */
    public function getData()
    {
        $query      = $this->buildQuery();
        $pagination = $this->getPagination();

        $rows       = $this->_getList($query, $pagination->limitstart, $pagination->limit);

        return $rows;
    }

    /**
     * Query
     */
    protected function buildQuery()
    {
        $app              = Factory::getApplication();
        $jemsettings      = \PlanjeagendaHelper::config();

        $filter_order     = $app->getUserStateFromRequest( 'com_planjeagenda.userelement.filter_order', 'filter_order', 'u.name', 'cmd' );
        $filter_order_Dir = $app->getUserStateFromRequest( 'com_planjeagenda.userelement.filter_order_Dir', 'filter_order_Dir', '', 'word' );

        $filter_order     = InputFilter::getInstance()->clean($filter_order, 'cmd');
        $filter_order_Dir = InputFilter::getInstance()->clean($filter_order_Dir, 'word');

        $search           = $app->getUserStateFromRequest('com_planjeagenda.userelement.filter_search', 'filter_search', '', 'string' );
        $search           = $this->_db->escape( trim(\Joomla\String\StringHelper::strtolower( $search ) ) );

        // start query
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(array('u.id', 'u.name', 'u.username', 'u.email'));
        $query->from('#__users as u');

        // where
        $where = array();
        $where[] = 'u.block = 0';

        /*
         * Search name
         */
        if ($search) {
            $where[] = ' LOWER(u.name) LIKE \'%'.$search.'%\' ';
        }

        $query->where($where);

        // ordering
        $orderby = '';
        $orderby = $filter_order.' '.$filter_order_Dir;

        $query->order($orderby);

        return $query;
    }

    /**
     * Method to get a pagination object
     *
     * @access public
     * @return integer
     */
    public function getPagination()
    {
        $app         = Factory::getApplication();
        $jemsettings = \PlanjeagendaHelper::config();

        $limit       = $app->getUserStateFromRequest('com_planjeagenda.userelement.limit', 'limit', $jemsettings->display_num, 'int');
        $limitstart  = $app->input->getInt('limitstart', 0);

        $query = $this->buildQuery();
        $total = $this->_getListCount($query);

        // Create the pagination object
        $pagination = new Pagination($total, $limitstart, $limit);

        return $pagination;
    }
}
