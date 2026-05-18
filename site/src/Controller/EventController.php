<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * Namespaced controller — delegates to legacy task controller.
 * J6 dispatcher resolves this class; it loads and extends the legacy class
 * which contains the actual task logic.
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Controller;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Service\Provider\Session;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\RouteHelper;
use KoelmanLabs\Component\Planjeagenda\Site\Service\IcsCalendarService;
use Laminas\Diactoros\Response;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;


/**
 * EventController — J6 namespaced proxy for PlanjeagendaControllerEvent.
 */
class EventController extends FormController
{
    protected $view_item = 'editevent';
    protected $view_list = 'eventslist';
    protected $_id = 0;
    
    /**
     * Method to add a new record.
     *
     * @return boolean True if the event can be added, false if not.
     */
    public function add()
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());
        }
    }
    
    /**
     * Method override to check if you can add a new record.
     *
     * @param  array An array of input data.
     *
     * @return boolean
     */
    protected function allowAdd($data = array())
    {
        $user        = \Joomla\CMS\Factory::getApplication()->getIdentity();
        $jemsettings = \PlanjeagendaHelper::config();
        
        // Admins always can add
        if ($user->authorise('core.create', 'com_planjeagenda')) {
            return true;
        }
        
        // Must be logged in
        if (!$user->id) {
            return false;
        }
        
        // Component setting: eventedit = -1 means all registered can add/edit
        $eventedit = isset($jemsettings->eventedit) ? (int)$jemsettings->eventedit : 0;
        return ($eventedit == -1);
    }
    
    /**
     * Method override to check if you can edit an existing record.
     *
     * @param  array  $data An array of input data.
     * @param  string $key  The name of the key for the primary key.
     *
     * @return boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $recordId    = (int) ($data[$key] ?? 0);
        $user        = \Joomla\CMS\Factory::getApplication()->getIdentity();
        $settingsTable = PlanjeagendaHelper::config(); // settings from Table
        
        // Admins always can edit
        if ($user->authorise('core.edit', 'com_planjeagenda')) {
            return true;
        }
        
        // Must be logged in
        if (!$user->id) {
            return false;
        }
        
        // Component setting: who can edit events?
        // eventedit = -2: only admins (handled above)
        // eventedit = -1: all registered users
        // eventedit = 0: only via eventowner setting
        $eventedit = isset($settingsTable->eventedit) ? (int)$settingsTable->eventedit : 0;
        
        if ($eventedit == -1) {
            // All registered users can edit
            return true;
        }
        
        // Check if owner editing is allowed
        $eventowner = isset($settingsTable->eventowner) ? (int)$settingsTable->eventowner : 0;
        if ($eventowner) {
            $record     = $this->getModel()->getItem($recordId);
            $created_by = $record->created_by ?? 0;
            if ($user->id == $created_by) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Method to cancel an edit.
     *
     * @param  string $key The name of the primary key of the URL variable.
     *
     * @return boolean True if access level checks pass, false otherwise.
     */
    public function cancel($key = 'a_id')
    {
        
        $app    = \Joomla\CMS\Factory::getApplication();
        $area    = $app->input->getString('area', ''); // form or eventlist
        
        // Check for request forgeries
        $this->checkToken('post');
        
        parent::cancel($key);
        
        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage());
    }
    
    /**
     * Method to edit an existing record.
     *
     * @param  string $key    The name of the primary key of the URL variable.
     * @param  string $urlVar The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return boolean True if access level check and checkout passes, false otherwise.
     */
    public function editCHANGE($key = null, $urlVar = 'a_id')
    {
        
        
        
        $app    = \Joomla\CMS\Factory::getApplication();
        $itemId = $app->input->getInt('a_id', 0);
        
        // Check edit permission
        if (!$this->allowEdit(['id' => $itemId], 'id')) {
            $app->enqueueMessage(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->setRedirect($this->getReturnPage());
            return false;
        }
        
        // Checkout the record
        $model = $this->getModel();
        if ($model && $itemId) {
            $table = $model->getTable();
            if ($table && method_exists($table, 'checkout')) {
                $table->checkout($app->getIdentity()->id, $itemId);
            }
        }
        
        // Set input vars so the editevent view can find the item
        $app->input->set('view',   'editevent');
        $app->input->set('layout', 'edit');
        $app->input->set('a_id',   $itemId);
        
        // Render the editevent view directly
        $sitePath = JPATH_SITE . '/components/com_planjeagenda';
        $viewFile  = $sitePath . '/views/editevent/view.html.php';
        if (file_exists($viewFile) && !class_exists('PlanjeagendaViewEditevent', false)) {
            require_once $viewFile;
        }
        if (class_exists('PlanjeagendaViewEditevent', false)) {
            $view = new \PlanjeagendaViewEditevent();
            $view->setLayout('edit');
            $view->display();
        }
        
        return true;
    }
    
    
    
    /**
     * Method to edit an existing record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key
     * (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if access level check and checkout passes, false otherwise.
     *
     * @since   1.6
     */
    public function edit($key = null, $urlVar = 'a_id')
    {
        $result = parent::edit($key, $urlVar);
        
        if (!$result) {
            $this->setRedirect(Route::_($this->getReturnPage(), false));
        }
        
        return $result;
    }
    
    
    
    
    
    
    /**
     * Method to add a new record based on existing record.
     *
     * @return boolean True if the event can be added, false if not.
     */
    public function copy()
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());
        }
    }
    
    /**
     * Method to get a model object, loading it if required.
     *
     * @param  string $name   The model name. Optional.
     * @param  string $prefix The class prefix. Optional.
     * @param  array  $config Configuration array for model. Optional.
     *
     * @return object The model.
     */
    public function getModelCHANGE($name = 'editevent', $prefix = '', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
    
    
    
    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.5
     */
    public function getModel($name = 'Form', $prefix = 'Site', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param  int    $recordId The primary key id for the item.
     * @param  string $urlVar   The name of the URL variable for the id.
     *
     * @return string The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
    {
        // Need to override the parent method completely.
        $jinput = Factory::getApplication()->input;
        $tmpl   = $jinput->getCmd('tmpl', '');
        $layout = $jinput->getCmd('layout', 'edit');
        $task   = $jinput->getCmd('task', '');
        $append = '';
        
        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }
        
        $append .= '&layout=edit';
        
        if ($recordId) {
            $append .= '&' . $urlVar . '=' . $recordId;
        } elseif (($task === 'copy') && ($fromId = $jinput->getInt('a_id', 0))) {
            $append .= '&from_id=' . $fromId;
        }
        
        $itemId = $jinput->getInt('Itemid', 0);
        $catId  = $jinput->getInt('catid', 0);
        $locId  = $jinput->getInt('locid', 0);
        $date   = $jinput->getCmd('date', '');
        $return = $this->getReturnPage();
        
        if ($itemId) {
            $append .= '&Itemid=' . $itemId;
        }
        
        if ($catId) {
            $append .= '&catid=' . $catId;
        }
        
        if ($locId) {
            $append .= '&locid=' . $locId;
        }
        
        if ($date) {
            $append .= '&date=' . $date;
        }
        
        if ($return) {
            $append .= '&return=' . base64_encode($return);
        }
        
        return $append;
    }
    
    /**
     * Get the return URL.
     *
     * If a "return" variable has been passed in the request
     *
     * @return  string  The return URL.
     *
     * @since   1.6
     */
    protected function getReturnPage()
    {
        $return = $this->input->get('return', null, 'base64');
        
        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Uri::base();
        }
        
        return base64_decode($return);
    }
    
    /**
     * Function that allows child controller access to model data
     * after the data has been saved.
     * Here used to trigger the klevents plugins, mainly the mailer.
     *
     * @param  JModel(Legacy)  $model      The data model object.
     * @param  array           $validData  The validated data.
     *
     * @return void
     */
    protected function _postSaveHook($model, $validData = array())
    {
        $task = $this->getTask();
        if ($task == 'save') {
            $isNew     = $model->getState('editevent.new');
            $this->_id = $model->getState('editevent.id');
            
            // trigger all klevents plugins
            PluginHelper::importPlugin('planjeagenda');
            \Joomla\CMS\Factory::getApplication()->triggerEvent('onEventEdited', array($this->_id, $isNew));
            
            // but show warning if mailer is disabled
            if (!self::isMailerEnabled()) {
                Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_GLOBAL_MAILERPLUGIN_DISABLED'), 'notice');
            }
        }
    }
    
    /**
     * Method to save a record.
     *
     * @param  string $key    The name of the primary key of the URL variable.
     * @param  string $urlVar The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return boolean True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = 'a_id')
    {
     
        // Check for request forgeries
        $this->checkToken('post');
        
        $result = parent::save($key, $urlVar);
        
        // If ok, redirect to the return page.
        if ($result) {
            $this->setRedirect($this->getReturnPage());
        }
        
        return $result;
    }
    
    /**
     * Saves the registration to the database
     */
    public function userregister()
    {
        // Check for request forgeries
        Session::checkToken() or die('Invalid Token');
        
        $app = Factory::getApplication();
        $input = $app->getInput();
        $id    = $input->getInt('rdid', 0);
        $rid   = $input->getInt('regid', 0);
        
        // Get the model
        $model = $this->getModel('Event', '');
        
        $reg = $model->getUserRegistration($id);
        if ($reg !== false && isset($reg->id) && $reg->id != $rid) {
            $msg = Text::_('com_planjeagenda_ALREADY_REGISTERED') . ' [id: ' . $reg->id . ']';
            $this->setRedirect(Route::_(PlanjeagendaHelperRoute::getEventRoute($id), false), $msg, 'error');
            $this->redirect();
            return;
        }
        
        $model->setId($id);
        $register_id = $model->userregister();
        
        if (!$register_id)
        {
            $msg = $model->getError();
            $this->setRedirect(Route::_(PlanjeagendaHelperRoute::getEventRoute($id), false), $msg, 'error');
            $this->redirect();
            return;
        }
        
        PlanjeagendaHelper::updateWaitingList($id);
        
        PluginHelper::importPlugin('planjeagenda');
        $dispatcher = \Joomla\CMS\Factory::getApplication();
        $places = isset($reg->places) ? $reg->places : 0;
        \Joomla\CMS\Factory::getApplication()->triggerEvent('onEventUserRegistered', array($register_id, $places));
        
        // Cache::clean() - Factory::getCache removed in J6, cache managed by Joomla automatically
        
        $msg = Text::_('com_planjeagenda_REGISTRATION_THANKS_FOR_RESPONSE');
        
        $this->setRedirect(Route::_(PlanjeagendaHelperRoute::getEventRoute($id), false), $msg);
    }
    
    /**
     * Deletes a registered user
     */
    public function delreguser()
    {
        // Check for request forgeries
        Session::checkToken() or die('Invalid Token');
        
        $id = Factory::getApplication()->input->getInt('rdid', 0);
        
        // Get/Create the model
        $model = $this->getModel('Event', '');
        
        $model->setId($id);
        $model->delreguser();
        
        PlanjeagendaHelper::updateWaitingList($id);
        
        PluginHelper::importPlugin('planjeagenda');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('onEventUserUnregistered', array($id));
        
        // Cache::clean() - Factory::getCache removed in J6, cache managed by Joomla automatically
        
        $msg = Text::_('com_planjeagenda_UNREGISTERED_SUCCESSFULL');
        $this->setRedirect(Route::_(PlanjeagendaHelperRoute::getEventRoute($id), false), $msg);
    }
}
