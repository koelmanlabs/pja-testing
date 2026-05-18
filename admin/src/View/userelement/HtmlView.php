<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Userelement;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $lists; protected $rows; protected $pagination;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');

        $filterOrder    = $app->getUserStateFromRequest('com_planjeagenda.userelement.filter_order',     'filter_order',     'u.name', 'cmd');
        $filterOrderDir = $app->getUserStateFromRequest('com_planjeagenda.userelement.filter_order_Dir', 'filter_order_Dir', '',       'word');
        $search         = $app->getUserStateFromRequest('com_planjeagenda.userelement.filter_search',    'filter_search',    '',       'string');
        $search         = $db->escape(trim(\Joomla\String\StringHelper::strtolower($search)));

        $app->getDocument()->setTitle(Text::_('COM_PLANJEAGENDA_SELECTATTENDEE'));
        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');

        $this->rows       = $this->get('Data');
        $this->pagination = $this->get('Pagination');
        $this->lists['order_Dir'] = $filterOrderDir;
        $this->lists['order']     = $filterOrder;
        $this->lists['search']    = $search;

        return parent::display($tpl);
    }
}
