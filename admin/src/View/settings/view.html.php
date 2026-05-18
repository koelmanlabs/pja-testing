<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * View class for the JEM Settings screen
 *
 * @package JEM
 */
class PlanjeagendaViewSettings extends PlanjeagendaAdminView
{
    protected $form;
    protected $data;
    protected $state;

    public function display($tpl = null)
    {
        $app         = Factory::getApplication();
        $document    = $app->getDocument();
        $form        = $this->get('Form');
        $data        = $this->get('Data');
        $state       = $this->get('State');
        $config      = $this->get('ConfigInfo');
        $jemsettings = $this->get('Data');
        $settings    = PlanjeagendaHelper::globalattribs();
        $this->document = $document;

        // Load css
        $wa = $document->getWebAssetManager();

        $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('planjeagenda.backend');
        $wa->registerStyle('planjeagenda.colorpicker', 'com_planjeagenda/colorpicker.css')->useStyle('planjeagenda.colorpicker');

        $style = '
            div.current fieldset.radio input {
                cursor: pointer;
            }';
        $document->addStyleDeclaration($style);

        // Check for model errors.
        if ($errors = $this->get('Errors')) {
            $app->enqueueMessage(implode('<br>', $errors), 'error');
            return false;
        }

        // Bind the form to the data.
        if ($form && $data) {
            $form->bind($data);
        }

        // Check for errors.
        $errors = $this->get('Errors');
        if (is_array($errors) && count($errors)) {
            $app->enqueueMessage(implode("\n", $errors), 'error');
            return false;
        }

        // Load Script
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->useScript('jquery');
        $wa->registerScript('planjeagenda.colorpicker_js', 'com_planjeagenda/colorpicker.js')->useScript('planjeagenda.colorpicker_js');

        if (!PlanjeagendaFactory::getUser()->authorise('core.manage', 'com_planjeagenda')) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $app->redirect('index.php?option=com_planjeagenda&view=main');
        }

        // mapping variables
        $this->form        = $form;
        $this->data        = $data;
        $this->state       = $state;
        $this->jemsettings = $jemsettings;
        $this->config      = $config;
        $this->settings       = $settings;

        // add toolbar
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since  1.6
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('com_planjeagenda_SETTINGS_TITLE'), 'settings');
        ToolbarHelper::apply('settings.apply');
        ToolbarHelper::save('settings.save');
        ToolbarHelper::cancel('settings.cancel');

        ToolbarHelper::divider();
        ToolbarHelper::inlinehelp();
        ToolBarHelper::help('settings', true, 'https://www.koelmanlabs.nl/documentation/manual/backend/settings');
    }

    protected function WarningIcon()
    {
        $url = Uri::root();
        $tip = '<span class="icon-info-circle" aria-hidden="true"></span>';

        return $tip;
    }
}
