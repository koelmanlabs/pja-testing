<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Groups;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class HtmlView extends BaseHtmlView
{
    protected $items; protected $pagination; protected $state;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
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
        $canDo = ContentHelper::getActions('com_planjeagenda');
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_GROUPS'), 'groups');
        if ($canDo->get('core.create'))     { ToolbarHelper::addNew('group.add'); }
        if ($canDo->get('core.edit'))       { ToolbarHelper::editList('group.edit'); ToolbarHelper::divider(); }
        if ($canDo->get('core.edit.state')) { ToolbarHelper::checkin('groups.checkin'); }
        ToolbarHelper::deleteList('COM_PLANJEAGENDA_CONFIRM_DELETE', 'groups.remove', 'JACTION_DELETE');
        ToolbarHelper::divider();
        ToolbarHelper::help('COM_PLANJEAGENDA_HELP');
    }
}
