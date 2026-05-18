<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * JEM Component Myevents Controller
 *
 * @package JEM
 *
 */
class PlanjeagendaControllerMyevents extends BaseController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Logic to publish events
     *
     * @access public
     * @return void
     */
    public function publish()
    {
        // Check for request forgeries
        Session::checkToken() or die('Invalid Token');

        $app = Factory::getApplication();
        $input = $app->input;

        $cid = $input->get('cid', array(), 'array');

        if (empty($cid)) {
            Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_SELECT_ITEM_TO_PUBLISH'), 'notice');
            $this->setRedirect(PlanjeagendaHelperRoute::getMyEventsRoute());
            return;
        }

        $model = $this->getModel('myevents');
        if (!$model->publish($cid, 1)) {
            echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
        }

        $total = count($cid);
        $msg   = $total.' '.Text::_('com_planjeagenda_EVENT_PUBLISHED');

        $this->setRedirect(PlanjeagendaHelperRoute::getMyEventsRoute(), $msg);
    }

    /**
     * Logic for canceling an event and proceed to add a venue
     */
    public function unpublish()
    {
        // Check for request forgeries
        Session::checkToken() or die('Invalid Token');

        $app = Factory::getApplication();
        $input = $app->input;

        $cid = $input->get('cid', array(), 'array');

        if (empty($cid)) {
            Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_SELECT_ITEM_TO_UNPUBLISH'), 'notice');
            $this->setRedirect(PlanjeagendaHelperRoute::getMyEventsRoute());
            return;
        }

        $model = $this->getModel('myevents');
        if (!$model->publish($cid, 0)) {
            echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
        }

        $total = count($cid);
        $msg   = $total.' '.Text::_('com_planjeagenda_EVENT_UNPUBLISHED');

        $this->setRedirect(PlanjeagendaHelperRoute::getMyEventsRoute(), $msg);
    }

    /**
     * Logic to trash events
     *
     * @access public
     * @return void
     */
    public function trash()
    {
        // Check for request forgeries
        Session::checkToken() or die('Invalid Token');

        $app = Factory::getApplication();
        $input = $app->input;

        $cid = $input->get('cid', array(), 'array');

        if (empty($cid)) {
            Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_SELECT_ITEM_TO_TRASH'), 'notice');
            $this->setRedirect(PlanjeagendaHelperRoute::getMyEventsRoute());
            return;
        }

        $model = $this->getModel('myevents');
        if (!$model->publish($cid, -2)) {
            echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
        }

        $total = count($cid);
        $msg   = $total.' '.Text::_('com_planjeagenda_EVENT_TRASHED');

        $this->setRedirect(PlanjeagendaHelperRoute::getMyEventsRoute(), $msg);
    }
}
