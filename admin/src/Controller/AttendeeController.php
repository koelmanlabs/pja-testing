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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;

class AttendeeController extends BaseController
{
    protected $default_view = 'attendee';

    /**
     * Constructor
     */
/**
     * redirect to events page
     */
    public function back()
    {
        $this->setRedirect('index.php?option=com_planjeagenda&view=attendees&eventid='. Factory::getApplication()->input->getInt('event', 0));
    }

    /**
     * logic for cancel an action
     *
     * @access public
     * @return void
     */
    public function cancel()
    {
        // Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $attendee = (new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\jem_register(\Joomla\CMS\Factory::getContainer()->get('DatabaseDriver')));
        $attendee->bind(Factory::getApplication()->input->post->getArray(/*get them all*/));
        $attendee->checkin();

        $this->setRedirect('index.php?option=com_planjeagenda&view=attendees&eventid='. Factory::getApplication()->input->getInt('event', 0));
    }

    /**
     * saves the attendee in the database
     *
     * @access public
     * @return void
     */
    public function save()
    {
        // Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        // Defining JInput
        $jinput = Factory::getApplication()->input;

        // retrieving task "apply"
        $task = $jinput->getCmd('task');

        // Retrieving $post
        $post = $jinput->post->getArray(/*get them all*/);

        // Retrieving email-setting
        $sendemail = $jinput->getInt('sendemail','0');

        // Retrieving event-id
        $eventid = $jinput->getInt('event');

        // the id in case of edit
        $id = (!empty($post['id']) ? $post['id'] : 0);

        $model = $this->getModel('Attendee');

        // Handle task 'save2copy' - reset id to store as new record, then like 'apply'.
        if ($task == 'save2copy') {
            $post['id'] = 0;
            $id = 0;
            $task = 'apply';
        }

        // handle changing the user - must also trigger onEventUserUnregistered
        $uid = (!empty($post['uid']) ? $post['uid'] : 0);
        if ($uid && $id) {
            $model->setId($id);
            $old_data = $model->getData();
        }
        $old_uid    = (!empty($old_data->uid)    ? $old_data->uid    : 0);
        $old_status = (!empty($old_data->status) ? $old_data->status : 0);

        if ($row = $model->store($post)) {
            if ($sendemail == 1) {
                PluginHelper::importPlugin('planjeagenda');
                $dispatcher = Factory::getApplication();
                // there was a user and it's overwritten by a new user -> send unregister mails
                if ($old_uid && ($old_uid != $uid)) {
                    Factory::getApplication()->triggerEvent('onEventUserUnregistered', array($old_data->event, $old_data));
                }
                // there is a new user which wasn't before -> send register mails
                if ($uid && (($old_uid != $uid) || ($row->status != $old_status))) {
                    Factory::getApplication()->triggerEvent('onEventUserRegistered', array($row->id));
                }
                // but show warning if mailer is disabled
                if (!self::isPlanjeagendaMailerEnabled()) {
                    Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_GLOBAL_MAILERPLUGIN_DISABLED'), 'notice');
                }
            }

            switch ($task)
            {
            case 'apply':
                // Redirect back to the edit screen.
                $link = 'index.php?option=com_planjeagenda&view=attendee&hidemainmenu=1&cid[]='.$row->id.'&eventid='.$row->event;
                break;

            case 'save2new':
                // Redirect back to the edit screen for new record.
                $link = 'index.php?option=com_planjeagenda&view=attendee&hidemainmenu=1&eventid='.$row->event;
                break;

            default:
                // Redirect to the list screen.
                $link = 'index.php?option=com_planjeagenda&view=attendees&eventid='.$row->event;
                break;
            }
            $msg = Text::_('com_planjeagenda_ATTENDEE_SAVED');

            $cache = Factory::getCache('com_planjeagenda');
            $cache->clean();
        } else {
            $msg     = '';
            $link     = 'index.php?option=com_planjeagenda&view=attendees&eventid='.$eventid;
        }
        $this->setRedirect($link, $msg);
    }

    public function selectUser()
    {
        $jinput = Factory::getApplication()->input;
        $jinput->set('view', 'userelement');
        parent::display();
    }

    /**
     * Direct DB check — bypasses PluginHelper static cache.
     */
    protected static function isPlanjeagendaMailerEnabled(): bool
    {
        try {
            $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery(
                $db->getQuery(true)
                    ->select('enabled')
                    ->from('#__extensions')
                    ->where('type = '    . $db->quote('plugin'))
                    ->where('folder = '  . $db->quote('planjeagenda'))
                    ->where('element = ' . $db->quote('planjeagenda_mailer'))
            );
            return (bool) $db->loadResult();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
