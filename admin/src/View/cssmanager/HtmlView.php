<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Cssmanager;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $files; protected $statusLinenumber;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $this->files             = $this->get('Files');
        $this->statusLinenumber  = $this->get('StatusLinenumber');
        if (count($errors = (array)($this->get('Errors') ?? []))) {
            $app->enqueueMessage(implode("\n", $errors), 'error'); return false;
        }
        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');
        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_CSSMANAGER_TITLE'), 'thememanager');
        ToolbarHelper::custom('cssmanager.back', 'back', '', Text::_('COM_PLANJEAGENDA_ATT_BACK'), false);
        ToolbarHelper::divider();
        ToolbarHelper::inlinehelp();
    }
}
