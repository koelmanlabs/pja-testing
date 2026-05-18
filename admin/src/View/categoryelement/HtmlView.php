<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Categoryelement;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $lists; protected $rows; protected $pagination; protected $filter_state;

    public function display($tpl = null)
    {
        $app    = Factory::getApplication();
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $itemid = $app->input->getInt('id', 0) . ':' . $app->input->getInt('Itemid', 0);

        $filterOrder    = $app->getUserStateFromRequest('com_planjeagenda.categoryelement.filter_order',     'filter_order',     'c.lft', 'cmd');
        $filterOrderDir = $app->getUserStateFromRequest('com_planjeagenda.categoryelement.filter_order_Dir', 'filter_order_Dir', '',      'word');
        $filterState    = $app->getUserStateFromRequest('com_planjeagenda.categoryelement.'.$itemid.'.filter_state',  'filter_state',  '', 'string');
        $search         = $app->getUserStateFromRequest('com_planjeagenda.categoryelement.'.$itemid.'.filter_search', 'filter_search', '', 'string');
        $search         = $db->escape(trim(\Joomla\String\StringHelper::strtolower($search)));

        $app->getDocument()->setTitle(Text::_('COM_PLANJEAGENDA_SELECT_CATEGORY'));
        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');

        $this->rows       = $this->get('Data');
        $this->pagination = $this->get('Pagination');
        $this->lists['state']     = HTMLHelper::_('grid.state', $filterState);
        $this->lists['order_Dir'] = $filterOrderDir;
        $this->lists['order']     = $filterOrder;
        $this->lists['search']    = $search;
        $this->filter_state = $filterState;

        return parent::display($tpl);
    }
}
