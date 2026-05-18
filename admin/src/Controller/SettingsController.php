<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class SettingsController extends BaseController
{

        public function __construct($config = [], $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // Map the apply task to the save method.
        $this->registerTask('apply', 'save');
    }

    /**
     * Method to check if you can add a new record.
     *
     * @return boolean
     */
    protected function allowEdit()
    {
        return Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_planjeagenda');
    }

    /**
     * Method to check if you can save a new or existing record.
     *
     * @return boolean
     */
    protected function allowSave()
    {
        return $this->allowEdit();
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param  string  The model name. Optional.
     * @param  string  The class prefix. Optional.
     * @param  array   Configuration data for model. Optional.
     *
     * @return object  The model.
     */
    public function getModel($name = 'Settings', $prefix = '', $config = array())
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Method to save the configuration data.
     * This saves data to Settings Table
     */
    public function save()
    {
        // Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = Factory::getApplication();
        $data = $app->input->get('jform', array(), 'array');

        $task = $this->getTask();
        $model = $this->getModel();
        $context = 'com_planjeagenda.edit.settings';

        // Access check.
        if (!$this->allowSave()) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_SAVE_NOT_PERMITTED'), 'warning');
            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_planjeagenda&view=settings', false));
            return false;
        }

        // Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            Factory::getApplication()->enqueueMessage($model->getError(), 'error');
            return false;
        }

        // Validate the posted data.
        $form = $model->getForm();
        $data = $model->validate($form, $data);

        // Check for validation errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                }
                else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(Route::_('index.php?option=com_planjeagenda&view=settings', false));
            return false;
        }

        // Attempt to save the data.
        if (!$model->store($data)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('JERROR_SAVE_FAILED', $model->getError()), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_planjeagenda&view=settings', false));
            return false;
        }

        $this->setMessage(Text::_('COM_PLANJEAGENDA_SETTINGS_SAVED'));

        // Redirect the user and adjust session state based on the chosen task.
        switch ($task)
        {
            case 'apply':
                // Reset the record data in the session.
                $app->setUserState($context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(Route::_('index.php?option=com_planjeagenda&view=settings', false));
                break;

            default:
                // Clear the record id and data from the session.
                $app->setUserState($context . '.id', null);
                $app->setUserState($context . '.data', null);

                // Redirect to the list screen.
                $this->setRedirect(Route::_('index.php?option=com_planjeagenda&view=main', false));
                break;
        }
    }

    /**
     * Cancel operation
     */
    public function cancel()
    {
        // Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        // Check if the user is authorized to do this.
        if (!Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_planjeagenda')) {
            Factory::getApplication()->redirect('index.php', Text::_('JERROR_ALERTNOAUTHOR'));
            return;
        }

        $this->setRedirect('index.php?option=com_planjeagenda');
    }

}
