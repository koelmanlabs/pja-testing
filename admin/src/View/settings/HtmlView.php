<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Settings;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $form; protected $data; protected $state; protected $config;

    public function display($tpl = null)
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();
        if (!$user->authorise('core.manage', 'com_planjeagenda')) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $app->redirect('index.php?option=com_planjeagenda&view=main');
            return;
        }

        $this->form        = $this->get('Form');
        $this->data        = $this->get('Data');
        $this->state       = $this->get('State');
        $this->config      = $this->get('ConfigInfo');

 
        $this->jemsettings = class_exists('PlanjeagendaHelper', false)
            ? \PlanjeagendaHelper::config()
            : new \stdClass();
        if (count($errors = (array)($this->get('Errors') ?? []))) {
            $app->enqueueMessage(implode("<br>", $errors), 'error'); return false;
        }
        if ($this->form && $this->data) { $this->form->bind($this->data); }

        $doc = $app->getDocument();
        $wa  = $doc->getWebAssetManager();
        $wa->registerStyle('planjeagenda.backend',    'com_planjeagenda/backend.css')   ->useStyle('planjeagenda.backend');
        $wa->registerStyle('planjeagenda.colorpicker', 'com_planjeagenda/colorpicker.css')->useStyle('planjeagenda.colorpicker');
        $wa->useScript('jquery');
        $wa->registerScript('planjeagenda.colorpicker_js', 'com_planjeagenda/colorpicker.js')->useScript('planjeagenda.colorpicker_js');
        $doc->addStyleDeclaration('div.current fieldset.radio input { cursor: pointer; }');

        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_SETTINGS_TITLE'), 'settings');
        ToolbarHelper::apply('settings.apply');
        ToolbarHelper::save('settings.save');
        ToolbarHelper::cancel('settings.cancel');
        ToolbarHelper::divider();
        ToolbarHelper::inlinehelp();
    }

    /**
     * Returns a warning icon HTML string (used in settings templates).
     */
    public function WarningIcon(): string
    {
        return '<span class="icon-warning-2" aria-hidden="true"></span>';
    }

}
