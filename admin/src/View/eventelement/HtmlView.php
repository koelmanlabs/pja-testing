<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Eventelement;
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

        $filterOrder    = $app->getUserStateFromRequest('com_planjeagenda.eventelement.filter_order',     'filter_order',     'a.dates', 'cmd');
        $filterOrderDir = $app->getUserStateFromRequest('com_planjeagenda.eventelement.filter_order_Dir', 'filter_order_Dir', '',        'word');
        $filterType     = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_type',   'filter_type',   0,  'int');
        $filterState    = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_state',  'filter_state',  '', 'string');
        $filterSearch   = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_search', 'filter_search', '', 'string');
        $filterSearch   = $db->escape(trim(\Joomla\String\StringHelper::strtolower($filterSearch)));

        $app->getDocument()->setTitle(Text::_('COM_PLANJEAGENDA_SELECTEVENT'));
        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');

        $this->rows       = $this->get('Data');
        $this->pagination = $this->get('Pagination');

        $filters = [
            HTMLHelper::_('select.option', '1', Text::_('COM_PLANJEAGENDA_EVENT_TITLE')),
            HTMLHelper::_('select.option', '2', Text::_('COM_PLANJEAGENDA_VENUE')),
            HTMLHelper::_('select.option', '3', Text::_('COM_PLANJEAGENDA_CITY')),
        ];
        $this->lists['filter']    = HTMLHelper::_('select.genericlist', $filters, 'filter_type', ['size'=>'1','class'=>'inputbox'], 'value', 'text', $filterType);
        $this->lists['order_Dir'] = $filterOrderDir;
        $this->lists['order']     = $filterOrder;
        $this->lists['search']    = $filterSearch;
        $this->filter_state       = $filterState;

        return parent::display($tpl);
    }
}
