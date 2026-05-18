<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Venue;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Venue-View
 */
class HtmlView extends BaseHtmlView
{

    public function __construct($config = array())
    {
        parent::__construct($config);

        // additional path for common templates + corresponding override path
        $this->addCommonTemplatePath();
    }

    /**
     * Creates the Venue View
     */
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        if ($this->getLayout() == 'calendar')
        {
            ### Venue Calendar view ###

            // initialize variables
            $app         = Factory::getApplication();
            $document    = $app->getDocument();
            $menu        = $app->getMenu();
            $menuitem    = $menu->getActive();
            $jemsettings = \PlanjeagendaHelper::config();
            $settings    = \PlanjeagendaHelper::globalattribs();
            $params      = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
            $uri         = Uri::getInstance();
            $pathway     = $app->getPathWay();
            $jinput      = $app->input;
            $print       = $jinput->getBool('print', false);
            $user        = Factory::getApplication()->getIdentity();
            $url          = Uri::root();
            $task        = $jinput->getCmd('task', '');
            $idVenue     = $jinput->getCmd('id', '');

            // Load css
            \PlanjeagendaHelper::loadCss('klevents');
            \PlanjeagendaHelper::loadCss('calendar');
            \PlanjeagendaHelper::loadCustomCss();
            \PlanjeagendaHelper::loadCustomTag();

            if ($print) {
                \PlanjeagendaHelper::loadCss('print');
                $document->setMetaData('robots', 'noindex, nofollow');
            }

            $venue = $this->get('Venue');
            // check for data error
            if (empty($venue)) {
                $app->enqueueMessage(Text::_('com_planjeagenda_VENUE_ERROR_VENUE_NOT_FOUND'), 'error');
                return false;
            }

            $evlinkcolor = $params->get('eventlinkcolor');
            $evbackgroundcolor = $params->get('eventbackgroundcolor');
            $currentdaycolor = $params->get('currentdaycolor');
            $eventandmorecolor = $params->get('eventandmorecolor');

            $style = '
            div#klevents .eventcontentinner a, div#klevents .eventandmore a {color:' . $evlinkcolor . ';}
            .eventcontentinner {background-color:'.$evbackgroundcolor .';}
            .eventandmore {background-color:' . $eventandmorecolor . ';}
            .today .daynum {background-color:' . $currentdaycolor . ';}';
            $document->addStyleDeclaration ($style);

            // add javascript (using full path - see issue #590)
            $document->addScript($url.'media/com_planjeagenda/js/calendar.js');
            // Retrieve year/month variables
            $year = $jinput->get('yearID', date("Y"),'int');
            $month = $jinput->get('monthID', date("m"),'int');

            // get data from model and set the month
            $model = $this->getModel('VenueCal');
            $model->setDate(mktime(0, 0, 1, $month, 1, $year));
            $rows = $this->get('Items','VenueCal');

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

            // create the pathway
            if ($task == 'archive') {
                $print_link = Route::_(\PlanjeagendaHelperRoute::getVenueRoute($venue->slug).'&task=archive&print=1&tmpl=component');
                $archive_link = Route::_('index.php?option=com_planjeagenda&view=venue&layout=calendar&id=' . $idVenue);
            } else {
                $print_link = Route::_(\PlanjeagendaHelperRoute::getVenueRoute($venue->slug).'&print=1&tmpl=component');
                $archive_link = $uri->toString() . (str_contains($uri->toString() ?? '','?')?'&':'?') . 'id=' . $venue->id;
            }

            // Check if the user has permission to add things
            $permissions = new \stdClass();
            $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
            $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');

            $itemid  = $jinput->getInt('Itemid', 0);
            $venueID = $jinput->getInt('id', $params->get('id'));

            $partItemid = ($itemid > 0) ? '&Itemid=' . $itemid : '';
            $partVenid = ($venueID > 0) ? '&id=' . $venueID : '';
            $partLocid = ($venueID > 0) ? '&locid=' . $venueID : '';
            $partDate = ($year ? ('&yearID=' . $year) : '') . ($month ? ('&monthID=' . $month) : '');
            $url_base = 'index.php?option=com_planjeagenda&view=venue&layout=calendar' . $partVenid . $partItemid;

            $print_link = Route::_($url_base . $partDate . '&print=1&tmpl=component');

            // init calendar
            $cal = new \PlanjeagendaCalendar($year, $month, 0);
            $cal->enableMonthNav($url_base . ($print ? '&print=1&tmpl=component' : ''));
            $cal->setFirstWeekDay($params->get('firstweekday',1));
            $cal->enableDayLinks('index.php?option=com_planjeagenda&view=day'.$partLocid);

            // map variables
            $this->rows          = $rows;
            $this->locid         = $venueID;
            $this->params        = $params;
            $this->jemsettings   = $jemsettings;
            $this->settings      = $settings;
            $this->permissions   = $permissions;
            $this->cal           = $cal;
            $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;
            $this->print_link    = $print_link;
            $this->archive_link  = $archive_link;
            $this->print         = $print;
            $this->ical_link     = $partDate;
            $this->task          = $task;

        }
        else
        {
            ### Venue List view ###

            // initialize variables
            $app         = Factory::getApplication();
            $document    = $app->getDocument();
            $menu        = $app->getMenu();
            $menuitem    = $menu->getActive();
            $jemsettings = \PlanjeagendaHelper::config();
            $settings    = \PlanjeagendaHelper::globalattribs();
            $params      = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams('com_planjeagenda'));
            $pathway     = $app->getPathWay ();
            $uri         = Uri::getInstance();
            $jinput      = $app->input;
            $task        = $jinput->getCmd('task', '');
            $print       = $jinput->getBool('print', false);
            $user        = Factory::getApplication()->getIdentity();
            $itemid      = $app->input->getInt('id', 0) . ':' . $app->input->getInt('Itemid', 0);

            // Load css
            \PlanjeagendaHelper::loadCss('klevents');
            \PlanjeagendaHelper::loadCustomCss();
            \PlanjeagendaHelper::loadCustomTag();

            if ($print) {
                \PlanjeagendaHelper::loadCss('print');
                $document->setMetaData('robots', 'noindex, nofollow');
            }

            // get data from model
            $rows  = $this->get('Items');
            $venue = $this->get('Venue');

            // check for data error
            if (empty($venue)) {
                $app->enqueueMessage(Text::_('com_planjeagenda_VENUE_ERROR_VENUE_NOT_FOUND'), 'error');
                return false;
            }

            // are events available?
            $noevents = (!$rows) ? 1 : 0;

            // Decide which parameters should take priority
            $useMenuItemParams = ($menuitem && $menuitem->query['option'] == 'com_planjeagenda'
                                            && $menuitem->query['view']   == 'venue'
                                            && (!isset($menuitem->query['layout']) || $menuitem->query['layout'] == 'default')
                                            && $menuitem->query['id']     == $venue->id);

            // get search & user-state variables
            $filter_order = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_order', 'filter_order', 'a.dates', 'cmd');
            $filter_order_DirDefault = 'ASC';
            // Reverse default order for dates in archive mode
            if($task == 'archive' && $filter_order == 'a.dates') {
                $filter_order_DirDefault = 'DESC';
            }
            $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_order_Dir', 'filter_order_Dir', $filter_order_DirDefault, 'word');
            $filter_type      = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_type', 'filter_type', 0, 'int');
            $search           = $app->getUserStateFromRequest('com_planjeagenda.venue.'.$itemid.'.filter_search', 'filter_search', '', 'string');
            $search_month     = $app->getUserStateFromRequest('com_planjeagenda.category.'.$itemid.'.filter_month', 'filter_month', '', 'string');

            // table ordering
            $lists['order_Dir'] = $filter_order_Dir;
            $lists['order']     = $filter_order;

            // Get image
            $limage = \PlanjeagendaImage::flyercreator($venue->locimage,'venue');

            // Add feed links
            $link = '&format=feed&id='.$venue->id.'&limitstart=';
            $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
            $document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
            $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
            $document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);

            // pathway, page title, page heading
            if ($useMenuItemParams) {
                $pagetitle   = $params->get('page_title', $menuitem->title ? $menuitem->title : $venue->venue);
                $pageheading = $params->get('page_heading', $pagetitle);
                $pathwayKeys = array_keys($pathway->getPathway());
                $lastPathwayEntryIndex = end($pathwayKeys);
                $pathway->setItemName($lastPathwayEntryIndex, $menuitem->title);
                //$pathway->setItemName(1, $menuitem->title);
            } else {
                $pagetitle   = $venue->venue;
                $pageheading = $pagetitle;
                $params->set('show_page_heading', 1); // ensure page heading is shown
                $pathway->addItem($pagetitle, Route::_(\PlanjeagendaHelperRoute::getVenueRoute($venue->slug)));
            }
            $pageclass_sfx = $params->get('pageclass_sfx');

            // create the pathway
            if ($task == 'archive') {
                $pathway->addItem (Text::_('com_planjeagenda_ARCHIVE'), Route::_(\PlanjeagendaHelperRoute::getVenueRoute($venue->slug).'&task=archive'));
                $print_link = Route::_(\PlanjeagendaHelperRoute::getVenueRoute($venue->slug).'&task=archive&print=1&tmpl=component');
                $pagetitle   .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
                $pageheading .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
            } else {
                //$pathway->addItem($venue->venue, Route::_(\PlanjeagendaHelperRoute::getVenueRoute($venue->slug)));
                $print_link = Route::_(\PlanjeagendaHelperRoute::getVenueRoute($venue->slug).'&print=1&tmpl=component');
            }
            $archive_link = Route::_(\PlanjeagendaHelperRoute::getVenueRoute($venue->slug));

            $params->set('page_heading', $pageheading);

            // Add site name to title if param is set
            if ($app->get('sitename_pagetitles', 0) == 1) {
                $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
            }
            elseif ($app->get('sitename_pagetitles', 0) == 2) {
                $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
            }

            // set Page title & Meta data
            $document->setTitle($pagetitle);
            $document->setMetaData('title', $pagetitle);
            $document->setMetadata('keywords', $venue->meta_keywords);
            $document->setDescription(strip_tags($venue->meta_description ?? ''));

            // Check if the user has permission to add things
            $permissions = new \stdClass();
            $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
            $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');

            // Check if the user has permission to edit-this venue
            $permissions->canEditVenue = $user->authorise('core.edit', 'com_planjeagenda');
            $permissions->canEditPublishVenue = $user->authorise('core.edit.state', 'com_planjeagenda');

            // Generate Venuedescription
            if (!$venue->locdescription == '' || !$venue->locdescription == '<br>') {
                // execute plugins
                $venue->text = $venue->locdescription;
                $venue->title = $venue->venue;
                PluginHelper::importPlugin ('content');
                $app->triggerEvent ('onContentPrepare', array ('com_planjeagenda.venue', &$venue, &$params, 0));
                $venuedescription = $venue->text;
            }

            // build the url
            if (!empty($venue->url) && !preg_match('%^http(s)?://%', $venue->url)) {
                $venue->url = 'https://' . $venue->url;
            }

            // prepare the url for output
            if (\Joomla\String\StringHelper::strlen($venue->url) > 35) {
                $venue->urlclean = $this->escape(\Joomla\String\StringHelper::substr($venue->url, 0, 35)) . '...';
            } else {
                $venue->urlclean = $this->escape($venue->url);
            }

            // create flag
            if ($venue->country) {
                $venue->countryimg = PlanjeagendaHelperCountries::getCountryFlag($venue->country);
            }

            // Create the pagination object
            $pagination = $this->get('Pagination');

            // filters
            $filters = array ();

            // ALL events have the same venue - so hide this from filter and list
            $jemsettings->showlocate = 0;

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
            $lists['filter'] = HTMLHelper::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

            // search filter
            $lists['search'] = $search;
            if(!empty($search_month)){
                $lists['month'] = $search_month;
            }

            // don't show venue-related columns on Venue view
            $lists['hide'] = array('venue' => 1);

            // mapping variables
            $this->lists            = $lists;
            $this->action           = $uri->toString();
            $this->rows             = $rows;
            $this->noevents         = $noevents;
            $this->venue            = $venue;
            $this->print_link       = $print_link;
            $this->archive_link     = $archive_link;
            $this->print            = $print;
            $this->params           = $params;
            $this->limage           = $limage;
            $this->venuedescription = $venuedescription;
            $this->pagination       = $pagination;
            $this->jemsettings      = $jemsettings;
            $this->settings         = $settings;
            $this->permissions      = $permissions;
            $this->show_status      = $permissions->canEditPublishVenue;
            $this->item             = $menuitem;
            $this->pagetitle        = $pagetitle;
            $this->task             = $task;
            $this->pageclass_sfx    = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;
        }

        parent::display($tpl);
    }
}
