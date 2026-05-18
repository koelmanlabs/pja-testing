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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * Eventelement-View
 */
class PlanjeagendaViewEventelement extends Htmlview {

    public function display($tpl = null)
    {
        $app = Factory::getApplication();

        //initialise variables
        $user        = PlanjeagendaFactory::getUser();
        $db          = Factory::getContainer()->get('DatabaseDriver');
        $jemsettings = PlanjeagendaAdmin::config();
        $document    = $app->getDocument();
        $itemid      = $app->input->getInt('id', 0) . ':' . $app->input->getInt('Itemid', 0);

        //get var
        $filter_order     = $app->getUserStateFromRequest('com_planjeagenda.eventelement.filter_order',     'filter_order', 'a.dates', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.eventelement.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $filter_type      = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_type',   'filter_type', 0, 'int');
        $filter_state     = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_state',  'filter_state', '', 'string');
        $filter_search    = $app->getUserStateFromRequest('com_planjeagenda.eventelement.'.$itemid.'.filter_search', 'filter_search', '', 'string');
        $filter_search    = $db->escape(trim(\Joomla\String\StringHelper::strtolower($filter_search)));

        //prepare the document
        $document->setTitle(Text::_('com_planjeagenda_SELECTEVENT'));

        // Load css
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('planjeagenda.backend');

        //Get data from the model
        $rows = $this->get('Data');
        $pagination = $this->get('Pagination');

        //publish unpublished filter
        //$lists['state']    = HTMLHelper::_('grid.state', $filter_state);

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;

        //Create the filter selectlist
        $filters = array();
        $filters[] = HTMLHelper::_('select.option', '1', Text::_('com_planjeagenda_EVENT_TITLE'));
        $filters[] = HTMLHelper::_('select.option', '2', Text::_('com_planjeagenda_VENUE'));
        $filters[] = HTMLHelper::_('select.option', '3', Text::_('com_planjeagenda_CITY'));
        //$filters[] = HTMLHelper::_('select.option', '4', Text::_('com_planjeagenda_CATEGORY'));
        $lists['filter'] = HTMLHelper::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

        // search filter
        $lists['search']= $filter_search;

        //assign data to template
        $this->lists         = $lists;
        $this->filter_state = $filter_state;
        $this->rows         = $rows;
        $this->pagination     = $pagination;
        $this->jemsettings     = $jemsettings;
        $this->user         = $user;

        parent::display($tpl);
    }
}
?>
