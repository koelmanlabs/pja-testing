<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Category;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Category-View
 */
class HtmlView extends BaseHtmlView
{
    protected $state;
    protected $items;
    protected $category;
    protected $children;
    protected $pagination;


    public function __construct($config = array())
    {
        parent::__construct($config);

        // additional path for common templates + corresponding override path
        $this->addCommonTemplatePath();
    }

    /**
     * Creates the Category View
     */
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        if ($this->getLayout() == 'calendar')
        {
            ### Category Calendar view ###

            //initialize variables
            $app         = Factory::getApplication();
            $document    = $app->getDocument();
            $jemsettings = \PlanjeagendaHelper::config();
            $settings    = \PlanjeagendaHelper::globalattribs();
            $user        = Factory::getApplication()->getIdentity();
            $menu        = $app->getMenu();
            $menuitem    = $menu->getActive();
            $params      = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
            $uri         = Uri::getInstance();
            $pathway     = $app->getPathWay();
            $print       = $app->input->getBool('print', false);
            $url          = Uri::root();
            // Load css
            \PlanjeagendaHelper::loadCss('klevents');
            \PlanjeagendaHelper::loadCss('calendar');
            \PlanjeagendaHelper::loadCustomCss();
            \PlanjeagendaHelper::loadCustomTag();

            if ($print) {
                \PlanjeagendaHelper::loadCss('print');
                $document->setMetaData('robots', 'noindex, nofollow');
            }

            $evlinkcolor       = $params->get('eventlinkcolor');
            $evbackgroundcolor = $params->get('eventbackgroundcolor');
            $currentdaycolor   = $params->get('currentdaycolor');
            $eventandmorecolor = $params->get('eventandmorecolor');

            $style = '
            div#klevents .eventcontentinner a, div#klevents .eventandmore a {color:' . $evlinkcolor . ';}
            .eventcontentinner {background-color:'.$evbackgroundcolor .';}
            .eventandmore {background-color:'.$eventandmorecolor .';}
            .today .daynum {background-color:'.$currentdaycolor.';}';
            $document->addStyleDeclaration($style);

            // add javascript (using full path - see issue #590)
            // HTMLHelper::_('script', 'media/com_planjeagenda/js/calendar.js');
            $document->addScript($url.'media/com_planjeagenda/js/calendar.js');

            // Retrieve date variables
            $year  = (int)$app->input->getInt('yearID', date("Y"));
            $month = (int)$app->input->getInt('monthID', date("m"));

            $catid = $app->input->getInt('id', 0);
            if (empty($catid)) {
                $catid = $params->get('id');
            }

            // get data from model and set the month
            $model = $this->getModel('CategoryCal');
            $model->setDate(mktime(0, 0, 1, $month, 1, $year));

            $category = $this->get('Category', 'CategoryCal');
            $rows     = $this->get('Items', 'CategoryCal');

            // Set Page title
            $pagetitle = $params->def('page_title', $menuitem->title);
            $params->def('page_heading', $params->get('page_title'));
            $pageclass_sfx = $params->get('pageclass_sfx');

            // Add site name to title if param is set
            if ($app->get('sitename_pagetitles', 0) == 1) {
                $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
            }
            elseif ($app->get('sitename_pagetitles', 0) == 2) {
                $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
            }

            $document->setTitle($pagetitle);
            $document->setMetaData('title', $pagetitle);

            // Check if the user has permission to add things
            $permissions = new \stdClass();
            $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
            $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');

            $itemid = $app->input->getInt('Itemid', 0);
            $partItemid = ($itemid > 0) ? '&Itemid='.$itemid : '';
            $partCatid = ($catid > 0) ? '&id=' . $catid : '';
            $url_base = 'index.php?option=com_planjeagenda&view=category&layout=calendar' . $partCatid . $partItemid;
            $partDate = ($year ? ('&yearID=' . $year) : '') . ($month ? ('&monthID=' . $month) : '');

            $print_link = Route::_($url_base . $partDate . '&print=1&tmpl=component');
            $archive_link = Route::_('index.php?option=com_planjeagenda&view=category');
            $archive_link = Route::_(\PlanjeagendaHelperRoute::getVenueRoute($catid));

            // init calendar
            $cal = new \PlanjeagendaCalendar($year, $month, 0);
            $cal->enableMonthNav($url_base . ($print ? '&print=1&tmpl=component' : ''));
            $cal->setFirstWeekDay($params->get('firstweekday', 1));
            $cal->enableDayLinks('index.php?option=com_planjeagenda&view=day&catid='.$catid);

            $this->rows          = $rows;
            $this->catid         = $catid;
            $this->params        = $params;
            $this->jemsettings   = $jemsettings;
            $this->settings      = $settings;
            $this->permissions   = $permissions;
            $this->cal           = $cal;
            $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;

            $this->print_link    = $print_link;
            $this->print         = $print;
            $this->ical_link     = $partDate;
            $this->archive_link  = $archive_link;

        }
        else
        {
            ### Category List view ###

            //initialize variables
            $app         = Factory::getApplication();
            $document    = $app->getDocument();
            $jemsettings = \PlanjeagendaHelper::config();
            $settings    = \PlanjeagendaHelper::globalattribs();
            $user        = Factory::getApplication()->getIdentity();
            $print       = $app->input->getBool('print', false);

            // get menu information
            $uri      = Uri::getInstance();
            $pathway  = $app->getPathWay();
            $menu     = $app->getMenu();
            $menuitem = $menu->getActive();

            // Load css
            \PlanjeagendaHelper::loadCss('klevents');
            \PlanjeagendaHelper::loadCustomCss();
            \PlanjeagendaHelper::loadCustomTag();

            if ($print) {
                \PlanjeagendaHelper::loadCss('print');
                $document->setMetaData('robots', 'noindex, nofollow');
            }

            // get data from model
            $state    = $this->get('State');
            $params   = $state->params;
            $items    = $this->get('Items');
            $category = $this->get('Category');
            $children = $this->get('Children');
            $parent   = $this->get('Parent');

            if (empty($category)) {
                $app->enqueueMessage(Text::_('JGLOBAL_CATEGORY_NOT_FOUND'), 'error');
                return false;
            } else if(!$category->user_has_access_category) {
                $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
                return false;
            }

            // are events available?
            $noevents = (!$items) ? 1 : 0;

            // Decide which parameters should take priority
            $useMenuItemParams = ($menuitem && $menuitem->query['option'] == 'com_planjeagenda'
                                            && $menuitem->query['view']   == 'category'
                                            && (!isset($menuitem->query['layout']) || $menuitem->query['layout'] == 'default')
                                            && $menuitem->query['id']     == $category->id);

            // get variables
            $itemid = $app->input->getInt('id', 0) . ':' . $app->input->getInt('Itemid', 0);

            $this->showsubcats      = (bool)$params->get('usecat', 1);
            $this->showemptysubcats = (bool)$params->get('showemptychilds', 1);
            $this->includechildevents = (bool)$params->get('includechildevents', 0);

            $filter_order     = $app->getUserStateFromRequest('com_planjeagenda.category.'.$itemid.'.filter_order', 'filter_order',     'a.dates', 'cmd');
            $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.category.'.$itemid.'.filter_order_Dir', 'filter_order_Dir',    '', 'word');
            $filter_type      = $app->getUserStateFromRequest('com_planjeagenda.category.'.$itemid.'.filter_filtertype', 'filter_type', 0, 'int');
            $search           = $app->getUserStateFromRequest('com_planjeagenda.category.'.$itemid.'.filter_search', 'filter_search', '', 'string');
            $search_month     = $app->getUserStateFromRequest('com_planjeagenda.category.'.$itemid.'.filter_month', 'filter_month', '', 'string');
            $task             = $app->input->get('task', '');

            // table ordering
            $lists['order_Dir'] = $filter_order_Dir;
            $lists['order']     = $filter_order;

            // search filter
            $filters = array();

            if ($jemsettings->showtitle == 1) {
                $filters[] = HTMLHelper::_('select.option', '1', Text::_('com_planjeagenda_TITLE'));
            }
            if ($jemsettings->showlocate == 1) {
                $filters[] = HTMLHelper::_('select.option', '2', Text::_('com_planjeagenda_VENUE'));
            }
            if ($jemsettings->showcity == 1) {
                $filters[] = HTMLHelper::_('select.option', '3', Text::_('com_planjeagenda_CITY'));
            }
            if ($jemsettings->showcat == 1) {
                $filters[] = HTMLHelper::_('select.option', '4', Text::_('com_planjeagenda_CATEGORY'));
            }
            if ($jemsettings->showstate == 1) {
                $filters[] = HTMLHelper::_('select.option', '5', Text::_('com_planjeagenda_STATE'));
            }
            $lists['filter'] = HTMLHelper::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'form-select'), 'value', 'text', $filter_type);

            // search filter
            $lists['search'] = $search;
            if(!empty($search_month)){
                $lists['month'] = $search_month;
            }

            // don't show column "Category" on Category view
            $lists['hide'] = array('category' => 1);

            // Add feed links
            $link = '&format=feed&id='.$category->id.'&limitstart=';
            $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
            $document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
            $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
            $document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);

            // create the pathway
            $cats    = new \PlanjeagendaCategories($category->id);
            $parents = $cats->getParentlist();

            foreach ($parents as $parent) {
                $pathway->addItem($this->escape($parent->catname), Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($parent->slug)) );
            }

            // Show page heading specified on menu item or category title as heading - idea taken from com_content.
            //
            // Check to see which parameters should take priority
            // If the current view is the active menuitem and an category view for this category, then the menu item params take priority
            if ($useMenuItemParams) {
                $pagetitle   = $params->get('page_title', $menuitem->title ? $menuitem->title : $category->catname);
                $pageheading = $params->get('page_heading', $pagetitle);
                $pathwayKeys = array_keys($pathway->getPathway());
                $lastPathwayEntryIndex = end($pathwayKeys);
                $pathway->setItemName($lastPathwayEntryIndex, $menuitem->title);
                //$pathway->setItemName(1, $menuitem->title);
            } else {
                $pagetitle   = $category->catname;
                $pageheading = $pagetitle;
                $params->set('show_page_heading', 1); // ensure page heading is shown
                $pathway->addItem($category->catname, Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($category->slug)) );
            }
            $pageclass_sfx = $params->get('pageclass_sfx');

            if ($task == 'archive') {
                $pathway->addItem(Text::_('com_planjeagenda_ARCHIVE'), Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($category->slug).'&task=archive'));
                $print_link = Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($category->id) .'&task=archive&print=1&tmpl=component');
                $pagetitle   .= ' - '.Text::_('com_planjeagenda_ARCHIVE');
                $pageheading .= ' - '.Text::_('com_planjeagenda_ARCHIVE');
            } else {
                $print_link = Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($category->id) .'&print=1&tmpl=component');
            }
            $archive_link = Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($category->id));

            $params->set('page_heading', $pageheading);

            // Add site name to title if param is set
            if ($app->get('sitename_pagetitles', 0) == 1) {
                $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
            }
            elseif ($app->get('sitename_pagetitles', 0) == 2) {
                $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
            }

            // Set Page title & Meta data
            $document->setTitle($pagetitle);
            $document->setMetaData('title', $pagetitle);
            $document->setMetadata('keywords', $category->meta_keywords);
            $document->setDescription(strip_tags($category->meta_description ?? ''));

            // Check if the user has permission to add things
            $permissions = new \stdClass();
            $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
            $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');

            // Create the pagination object
            $pagination = $this->get('Pagination');

            // Generate Categorydescription
            if (empty ($category->description)) {
                $description = Text::_('com_planjeagenda_NO_DESCRIPTION');
            } else {
                // execute plugins
                $category->text  = $category->description;
                $category->title = $category->catname;
                PluginHelper::importPlugin('content');
                $app->triggerEvent('onContentPrepare', array('com_planjeagenda.category', &$category, &$params, 0));
                $description = $category->text;
            }

            $cimage = \PlanjeagendaImage::flyercreator($category->image,'category');

            $this->lists         = $lists;
            $this->action        = $uri->toString();
            $this->archive_link  = $archive_link;
            $this->cimage        = $cimage;
            $this->rows          = $items;
            $this->noevents      = $noevents;
            $this->print_link    = $print_link;
            $this->print         = $print;
            $this->params        = $params;
            $this->dellink       = $permissions->canAddEvent; // deprecated
            $this->permissions   = $permissions;
            $this->task          = $task;
            $this->description   = $description;
            $this->pagination    = $pagination;
            $this->jemsettings   = $jemsettings;
            $this->settings      = $settings;
            $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;
            $this->maxLevel      = $params->get('maxLevel', -1);
            $this->category      = $category;
            $this->children      = array($category->id => $children);
            $this->parent        = $parent;
            $this->user          = $user;
        }

        parent::display($tpl);
    }
}
