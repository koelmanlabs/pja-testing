<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Categories;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class HtmlView extends BaseHtmlView
{
    protected $items; protected $pagination; protected $state;
    protected $ordering = []; protected $f_levels;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        if ($this->pagination === null) {
            $this->pagination = new \Joomla\CMS\Pagination\Pagination(0, 0, 20);
        }
        if (count($errors = (array)($this->get('Errors') ?? []))) {
            $app->enqueueMessage(implode("\n", $errors), 'error'); return false;
        }
        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');
        foreach (($this->items ?? []) as &$item) {
            $this->ordering[$item->parent_id][] = $item->id;
        }
        $opts = [];
        for ($i = 1; $i <= 10; $i++) { $opts[] = HTMLHelper::_('select.option', (string)$i, Text::_('J'.$i)); }
        $this->f_levels = $opts;
        $this->addToolbar();
        
        
        // Haal het filterformulier en de actieve filters op
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        $canDo = ContentHelper::getActions('com_planjeagenda');
        $user  = Factory::getApplication()->getIdentity();
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_CATEGORIES'), 'elcategories');
        if ($canDo->get('core.create'))     { ToolbarHelper::addNew('category.add'); }
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            ToolbarHelper::editList('category.edit'); ToolbarHelper::divider();
        }
        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::publish('categories.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('categories.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('categories.archive');
        }
        if ($user->authorise('core.admin')) { ToolbarHelper::checkin('categories.checkin'); }
        if (($this->state?->get('filter.published') ?? 0) == -2 && $canDo->get('core.delete')) {
            ToolbarHelper::deleteList('COM_PLANJEAGENDA_CONFIRM_DELETE', 'categories.remove', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.edit.state')) {
            ToolbarHelper::trash('categories.trash');
        }
        if ($canDo->get('core.admin')) {
            ToolbarHelper::divider();
            ToolbarHelper::custom('categories.rebuild', 'refresh', '', 'JTOOLBAR_REBUILD', false);
        }
        ToolbarHelper::divider();
        ToolbarHelper::help('COM_PLANJEAGENDA_HELP');
    }
}
