<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Log\Log;

class AttendeesController extends BaseController
{
    protected $default_view = 'attendees';

    /**
     * Constructor
     */
        public function __construct($config = [], $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // Register Extra task
        $this->registerTask('add',   'edit');
        $this->registerTask('apply', 'save');

        $this->registerTask('onWaitinglist',  'toggleStatus');
        $this->registerTask('offWaitinglist', 'toggleStatus');

        $this->registerTask('setNotAttending','setStatus');
        $this->registerTask('setAttending',   'setStatus');
        $this->registerTask('setWaitinglist', 'setStatus');
    }

    /**
     * Delete attendees
     *
     * @return true on sucess
     * @access private
     */
    public function remove()
    {
        // Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $jinput = Factory::getApplication()->input;
        $cid = $jinput->get('cid',  0, 'array');
        $eventid = $jinput->getInt('eventid');

        if (!is_array($cid) || count($cid) < 1) {
            throw new \Exception(Text::_('com_planjeagenda_SELECT_ITEM_TO_DELETE'), 500);
        }

        $total = count($cid);

        PluginHelper::importPlugin('planjeagenda');
        $dispatcher = Factory::getApplication();

        $modelAttendeeList = $this->getModel('Attendees');
        $modelAttendeeItem = $this->getModel('Attendee');

        // We need information about every entry to delete for mailer.
        // But we should first delete the entry and than on success send the mails.
        foreach ($cid as $reg_id) {
            $modelAttendeeItem->setId($reg_id);
            $entry = $modelAttendeeItem->getData();
            if ($modelAttendeeList->remove(array($reg_id))) {
                Factory::getApplication()->triggerEvent('onEventUserUnregistered', array($entry->event, $entry));
            } else {
                $error = true;
            }
        }
        if (!empty($error)) {
            echo "<script> alert('" . $modelAttendeeList->getError() . "'); window.history.go(-1); </script>\n";
        }

        $cache = Factory::getCache('com_planjeagenda');
        $cache->clean();

        $msg = $total . ' ' . Text::_('com_planjeagenda_REGISTERED_USERS_DELETED');

        $this->setRedirect('index.php?option=com_planjeagenda&view=attendees&eventid=' . $eventid, $msg);
    }

    /**
     * Function to export
     */
    public function export()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv; charset=utf-8');
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="attendees_'.date('Y-m-d').'.csv"');
        header('Content-Transfer-Encoding: binary');
        header('Pragma: no-cache');

        echo "\xEF\xBB\xBF"; // Add BOM

        $model = $this->getModel('Attendees');
        $model->getCsv();
        die();
    }

    /**
     * redirect to events page
     */
    public function back()
    {
        $this->setRedirect('index.php?option=com_planjeagenda&view=events');
    }

    /**
     * Function to change status
     */
    public function toggleStatus()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $app  = Factory::getApplication();
        $pks  = $app->input->get('cid', array(), 'array');
        $task = $this->getTask();

        if (empty($pks)) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'warning');
        } else {
            \Joomla\Utilities\ArrayHelper::toInteger($pks);
            $model = $this->getModel('Attendee');

            PluginHelper::importPlugin('planjeagenda');
            $dispatcher = Factory::getApplication();

            foreach ($pks AS $pk) {
                $model->setId($pk);
                $attendee = $model->getData();
                $res = $model->toggle();

                if ($res) {
                    Factory::getApplication()->triggerEvent('onUserOnOffWaitinglist', array($pk));

                    if ($attendee->waiting) {
                        $msg = Text::_('com_planjeagenda_ADDED_TO_ATTENDING');
                    } else {
                        $msg = Text::_('com_planjeagenda_ADDED_TO_WAITING');
                    }
                    $type = 'message';
                } else {
                    $msg = Text::_('com_planjeagenda_WAITINGLIST_TOGGLE_ERROR') . ': ' . $model->getError();
                    $type = 'error';
                }

                if ($task !== 'toggleStatus') {
                    $app->enqueueMessage($msg, $type);
                }
            }
        }

        if ($task === 'toggleStatus') {
            # here we are selecting more rows so a general message would be better
            $msg = Text::_('com_planjeagenda_ATTENDEES_CHANGEDSTATUS');
            $type = "message";
            $app->enqueueMessage($msg, $type);
        }

        $this->setRedirect('index.php?option=com_planjeagenda&view=attendees&eventid=' . $attendee->event);
        $this->redirect();
    }

    /**
     * logic to create the edit attendee view
     *
     * @access public
     * @return void
     *
     */
    public function edit()
    {
        // Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $jinput = Factory::getApplication()->input;
        $jinput->set('view', 'attendee');
        // 'attendee' expects event id as 'event' not 'id'
        $jinput->set('event', $jinput->getInt('eventid'));
        $jinput->set('id', null);
        $jinput->set('hidemainmenu', '1');

        parent::display();
    }

    /**
     * Method to change status of selected rows.
     *
     * @return  void
     */
    public function setStatus()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $app = Factory::getApplication();
        $user = $app->getIdentity();

        $eventid = $app->input->getInt('eventid');
        $ids     = $app->input->get('cid', array(), 'array');
        $values  = array('setWaitinglist' => 2, 'setAttending' => 1, 'setInvited' => 0, 'setNotAttending' => -1);
        $task    = $this->getTask();
        $value   = \Joomla\Utilities\ArrayHelper::getValue($values, $task, 0, 'int');

        if (empty($ids))
        {
            $message = Text::_('JERROR_NO_ITEMS_SELECTED');
            Factory::getApplication()->enqueueMessage($message, 'warning');
        }
        else
        {
            // Get the model.
            $model = $this->getModel('Attendee');

            // Publish the items.
            if (!$model->setStatus($ids, $value))
            {
                $message = $model->getError();
                \PlanjeagendaHelper::addLogEntry($message, __METHOD__, Log::ERROR);
                Factory::getApplication()->enqueueMessage($message, 'warning');
            }
            else
            {
                PluginHelper::importPlugin('planjeagenda');
                $dispatcher = Factory::getApplication();

                switch ($value) {
                    case -1:
                        $message = Text::plural('com_planjeagenda_ATTENDEES_N_ITEMS_NOTATTENDING', count($ids));
                        foreach ($ids AS $pk) {
                            // onEventUserUnregistered($eventid, $record, $recordid)
                            Factory::getApplication()->triggerEvent('onEventUserUnregistered', array($eventid, false, $pk));
                        }
                        break;
                    case 0:
                        $message = Text::plural('com_planjeagenda_ATTENDEES_N_ITEMS_INVITED', count($ids));
                        foreach ($ids AS $pk) {
                            // onEventUserRegistered($recordid)
                            Factory::getApplication()->triggerEvent('onEventUserRegistered', array($pk));
                        }
                        break;
                    case 1:
                        $message = Text::plural('com_planjeagenda_ATTENDEES_N_ITEMS_ATTENDING', count($ids));
                        foreach ($ids AS $pk) {
                            // onEventUserRegistered($recordid)
                            Factory::getApplication()->triggerEvent('onEventUserRegistered', array($pk));
                        }
                        break;
                    case 2:
                        $message = Text::plural('com_planjeagenda_ATTENDEES_N_ITEMS_WAITINGLIST', count($ids));
                        foreach ($ids AS $pk) {
                            // onUserOnOffWaitinglist($recordid)
                            Factory::getApplication()->triggerEvent('onUserOnOffWaitinglist', array($pk));
                        }
                        break;
                }

                \PlanjeagendaHelper::addLogEntry($message, __METHOD__, Log::DEBUG);
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_planjeagenda&view=attendees&eventid=' . $eventid, false), $message);
    }
}
