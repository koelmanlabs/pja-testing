<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Search;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
/**
 * Search-View
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Creates the Simple List View
     */
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        // initialize variables
        $app          = Factory::getApplication();
        $document     = $app->getDocument();
        $jemsettings  = \PlanjeagendaHelper::config();
        $settings     = \PlanjeagendaHelper::globalattribs();
        $menu         = $app->getMenu();
        $menuitem     = $menu->getActive();
        $params       = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
        $uri          = Uri::getInstance();
        $pathway      = $app->getPathWay();
        $url           = Uri::root();
        $model        = $this->getModel('search');
     // $user         = Factory::getApplication()->getIdentity();

        // Decide which parameters should take priority
        $useMenuItemParams = ($menuitem && $menuitem->query['option'] == 'com_planjeagenda'
            && $menuitem->query['view'] == 'search');

        // Load css
        \PlanjeagendaHelper::loadCss('klevents');
        \PlanjeagendaHelper::loadCustomCss();
        \PlanjeagendaHelper::loadCustomTag();

        // Load Script
        // HTMLHelper::_('script', 'com_planjeagenda/search.js', false, true);
        $document->addScript($url.'media/com_planjeagenda/js/search.js');

        $filter_continent = $app->getUserStateFromRequest('com_planjeagenda.search.filter_continent', 'filter_continent', '', 'string');
        $filter_country   = $app->getUserStateFromRequest('com_planjeagenda.search.filter_country', 'filter_country', '', 'string');
        $filter_city      = $app->getUserStateFromRequest('com_planjeagenda.search.filter_city', 'filter_city', '', 'string');
        $filter_date_from = $app->getUserStateFromRequest('com_planjeagenda.search.filter_date_from', 'filter_date_from', '', 'string');
        $filter_date_to   = $app->getUserStateFromRequest('com_planjeagenda.search.filter_date_to', 'filter_date_to', '', 'string');
        $filter_category  = $app->getUserStateFromRequest('com_planjeagenda.search.filter_category', 'filter_category', 0, 'int');
        $task             = $app->input->getCmd('task', '');

        if(empty($filter_continent) && empty($filter_country)){
            $filter_country = $jemsettings->defaultCountry;
            $filter_continent = $model->getContinentFromCountry($filter_country);
        }

        // get data from model
        $rows = $this->get('Data');

        // are events available?
        $noevents = (!$rows) ? 1 : 0;

        // Check to see which parameters should take priority
        if ($useMenuItemParams) {
            // Menu item params take priority
            $pagetitle = $params->def('page_title', $menuitem ? $menuitem->title : Text::_('com_planjeagenda_SEARCH'));
            $pageheading = $params->def('page_heading', $pagetitle);
            $pathwayKeys = array_keys($pathway->getPathway());
            $lastPathwayEntryIndex = end($pathwayKeys);
            $pathway->setItemName($lastPathwayEntryIndex, $menuitem->title);
            //$pathway->setItemName(1, $menuitem->title);
        } else {
            $pagetitle = Text::_('com_planjeagenda_SEARCH');
            $pageheading = $pagetitle;
            $params->set('introtext', ''); // there is no introtext in that case
            $params->set('showintrotext', 0);
            $pathway->addItem(1, $pagetitle);
        }
        $pageclass_sfx = $params->get('pageclass_sfx');

        if ($task == 'archive') {
            $pathway->addItem(Text::_('com_planjeagenda_ARCHIVE'), Route::_('index.php?option=com_planjeagenda&view=search&task=archive'));
            $pagetitle   .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
            $pageheading .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
        }
        $pageclass_sfx = $params->get('pageclass_sfx');

        $params->set('page_heading', $pageheading);

        // Add site name to title if param is set
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
        }

        // Set Page title
        $document->setTitle($pagetitle);
        $document->setMetadata('title' , $pagetitle);

        // No permissions required/useful on this view
        $permissions = new \stdClass();

        // create select lists
        $lists    = $this->_buildSortLists();

        if ($lists['filter']) {
            //$uri->setVar('filter', $app->input->getString('filter', ''));
            //$filter        = $app->getUserStateFromRequest('com_planjeagenda.klevents.filter', 'filter', '', 'string');
            $uri->setVar('filter', $lists['filter']);
            $uri->setVar('filter_type', $app->input->getString('filter_type', ''));
        } else {
            $uri->delVar('filter');
            $uri->delVar('filter_type');
        }

        // Cause of group limits we can't use class here to build the categories tree
        $categories   = $this->get('CategoryTree');
        $catoptions   = array();
        $catoptions[] = HTMLHelper::_('select.option', '1', Text::_('com_planjeagenda_SELECT_CATEGORY'));
        $catoptions   = array_merge($catoptions, \PlanjeagendaCategories::getcatselectoptions($categories));
        $selectedcats = ($filter_category) ? array($filter_category) : array();

        // build selectlists
        $lists['categories'] = HTMLHelper::_('select.genericlist', $catoptions, 'filter_category', array('size'=>'1', 'class'=>'form-select'), 'value', 'text', $selectedcats);

        // Create the pagination object
        $pagination = $this->get('Pagination');

        // date filter
        $lists['date_from'] = HTMLHelper::_('calendar', $filter_date_from, 'filter_date_from', 'filter_date_from', '%Y-%m-%d', array('class'=>"inputbox", 'placeholder' => Text::_('com_planjeagenda_SEARCH_FROM'), 'showTime' => false));
        $lists['date_to']   = HTMLHelper::_('calendar', $filter_date_to, 'filter_date_to', 'filter_date_to', '%Y-%m-%d', array('class'=>"inputbox", 'placeholder' => Text::_('com_planjeagenda_SEARCH_TO'), 'showTime' => false));

        // country filter
        $continents = array();
        $continents[] = HTMLHelper::_('select.option', '',   Text::_('com_planjeagenda_SELECT_CONTINENT'));
        $continents[] = HTMLHelper::_('select.option', 'AF', Text::_('com_planjeagenda_AFRICA'));
        $continents[] = HTMLHelper::_('select.option', 'AS', Text::_('com_planjeagenda_ASIA'));
        $continents[] = HTMLHelper::_('select.option', 'EU', Text::_('com_planjeagenda_EUROPE'));
        $continents[] = HTMLHelper::_('select.option', 'NA', Text::_('com_planjeagenda_NORTH_AMERICA'));
        $continents[] = HTMLHelper::_('select.option', 'SA', Text::_('com_planjeagenda_SOUTH_AMERICA'));
        $continents[] = HTMLHelper::_('select.option', 'OC', Text::_('com_planjeagenda_OCEANIA'));
        $continents[] = HTMLHelper::_('select.option', 'AN', Text::_('com_planjeagenda_ANTARCTICA'));
        $lists['continents'] = HTMLHelper::_('select.genericlist', $continents, 'filter_continent', array('class'=>'form-select'), 'value', 'text', $filter_continent);
        unset($continents);

        // country filter
        $countries = array();
        $countries[] = HTMLHelper::_('select.option', '', Text::_('com_planjeagenda_SELECT_COUNTRY'));
        $countries = array_merge($countries, $this->get('CountryOptions'));
        $lists['countries'] = HTMLHelper::_('select.genericlist', $countries, 'filter_country', array('class'=>'form-select'), 'value', 'text', $filter_country);
        unset($countries);

        // city filter
        if ($filter_country) {
            $cities = array();
            $cities[] = HTMLHelper::_('select.option', '', Text::_('com_planjeagenda_SELECT_CITY'));
            $cities = array_merge($cities, $this->get('CityOptions'));
            $lists['cities'] = HTMLHelper::_('select.genericlist', $cities, 'filter_city', array('class'=>'form-select'), 'value', 'text', $filter_city);
            unset($cities);
        }

        $this->lists            = $lists;
        $this->action           = $uri->toString();
        $this->rows             = $rows;
        $this->task             = $task;
        $this->noevents         = $noevents;
        $this->params           = $params;
        $this->pagination       = $pagination;
        $this->jemsettings      = $jemsettings;
        $this->settings         = $settings;
        $this->permissions      = $permissions;
        $this->pagetitle        = $pagetitle;
        $this->filter_continent = $filter_continent;
        $this->filter_country   = $filter_country;
        $this->document         = $document;
        $this->pageclass_sfx    =$pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;

        parent::display($tpl);
    }

    /**
     * Method to build the sortlists
     *
     * @access private
     * @return array
     *
     */
    protected function _buildSortLists()
    {
        $app = Factory::getApplication();
        $task = $app->input->getCmd('task', '');

        $filter_order = $app->input->getCmd('filter_order', 'a.dates');
        $filter_order_DirDefault = 'ASC';
        // Reverse default order for dates in archive mode
        if ($task == 'archive' && $filter_order == 'a.dates') {
            $filter_order_DirDefault = 'DESC';
        }
        $filter_order_Dir = $app->input->get('filter_order_Dir', $filter_order_DirDefault);
        $filter           = $app->getUserStateFromRequest('com_planjeagenda.search.filter_search', 'filter_search', '', 'string');
        $filter_type      = $app->input->getString('filter_type', '');

        $sortselects = array();
        $sortselects[] = HTMLHelper::_('select.option', 'title', Text::_('com_planjeagenda_TABLE_TITLE'));
        $sortselects[] = HTMLHelper::_('select.option', 'venue', Text::_('com_planjeagenda_TABLE_LOCATION'));
        $sortselect    = HTMLHelper::_('select.genericlist', $sortselects, 'filter_type', array('size'=>'1','class'=>'form-select'), 'value', 'text', $filter_type);

        $lists['order_Dir']    = $filter_order_Dir;
        $lists['order']        = $filter_order;
        $lists['filter']       = $filter;
        $lists['filter_types'] = $sortselect;

        return $lists;
    }
}
