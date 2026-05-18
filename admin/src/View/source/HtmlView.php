<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Source;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Client\ClientHelper;

class HtmlView extends BaseHtmlView
{
    protected $form; protected $ftp; protected $source; protected $state; protected $template;

    public function display($tpl = null)
    {
        $this->form     = $this->get('Form');
        $this->ftp      = ClientHelper::setCredentialsFromRequest('ftp');
        $this->source   = $this->get('Source');
        $this->state    = $this->get('State');
        $this->template = $this->get('Template');
        if (count($errors = (array)($this->get('Errors') ?? []))) {
            Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error'); return false;
        }
        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        $canDo = ContentHelper::getActions('com_planjeagenda');
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_CSSMANAGER_EDIT_FILE'), 'thememanager');
        if ($canDo->get('core.edit')) {
            ToolbarHelper::apply('source.apply');
            ToolbarHelper::save('source.save');
        }
        ToolbarHelper::cancel('source.cancel', 'JTOOLBAR_CLOSE');
    }
}
