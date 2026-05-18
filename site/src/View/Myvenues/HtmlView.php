<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Myvenues;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

/**
 * View: MyVenues
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Creates the Myvenues View
     */
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        // Get data from model
        $venues       = $this->get('Venues');
        $pagination   = $this->get('VenuesPagination');

        // initialize variables
        $app          = Factory::getApplication();
        $document     = $app->getDocument();
        $jemsettings  = \PlanjeagendaHelper::config();
        $settings     = \PlanjeagendaHelper::globalattribs();
        $menu         = $app->getMenu();
        $menuitem     = $menu->getActive();
        $params       = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
        $uri          = Uri::getInstance();
        $user         = Factory::getApplication()->getIdentity();
        $userId       = $user->id;
        $pathway      = $app->getPathWay();
        $jinput       = $app->input;
        $print        = $jinput->getBool('print', false);
        $task         = $jinput->getCmd('task', '');

        // redirect if not logged in
        $this->needLoginFirst = 0;
        if (!$user->id) {
            $app->enqueueMessage(Text::_('com_planjeagenda_NEED_LOGGED_IN'), 'error');
            $this->needLoginFirst=1;
        }else {
            // Decide which parameters should take priority
            $useMenuItemParams = ($menuitem && $menuitem->query['option'] == 'com_planjeagenda'
                && $menuitem->query['view'] == 'myvenues');

            // Load css
            \PlanjeagendaHelper::loadCss('klevents');
            \PlanjeagendaHelper::loadCustomCss();
            \PlanjeagendaHelper::loadCustomTag();

            if ($print) {
                \PlanjeagendaHelper::loadCss('print');
                $document->setMetaData('robots', 'noindex, nofollow');
            }

            // are no venues available?
            $novenues = (!$venues) ? 1 : 0;

            // get variables
            $filter_order     = $app->getUserStateFromRequest('com_planjeagenda.myvenues.filter_order', 'filter_order', 'l.venue', 'cmd');
            $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.myvenues.filter_order_Dir', 'filter_order_Dir', '', 'word');
            // $filter_state  = $app->getUserStateFromRequest('com_planjeagenda.myvenues.filter_state', 'filter_state',     '*', 'word');
            $filter           = $app->getUserStateFromRequest('com_planjeagenda.myvenues.filter', 'filter', 0, 'int');
            $search           = $app->getUserStateFromRequest('com_planjeagenda.myvenues.filter_search', 'filter_search', '', 'string');

            // search filter
            $filters = array();

            // Workaround issue #557: Show venue name always.
            $jemsettings->showlocate = 1;

            //if ($jemsettings->showtitle == 1) {
            //    $filters[] = HTMLHelper::_('select.option', '1', Text::_('com_planjeagenda_TITLE'));
            //}
            if ($jemsettings->showlocate == 1) {
                $filters[] = HTMLHelper::_('select.option', '2', Text::_('com_planjeagenda_VENUE'));
            }
            if ($jemsettings->showcity == 1) {
                $filters[] = HTMLHelper::_('select.option', '3', Text::_('com_planjeagenda_CITY'));
            }
            //if ($jemsettings->showcat == 1) {
            //    $filters[] = HTMLHelper::_('select.option', '4', Text::_('com_planjeagenda_CATEGORY'));
            //}
            if ($jemsettings->showstate == 1) {
                $filters[] = HTMLHelper::_('select.option', '5', Text::_('com_planjeagenda_STATE'));
            }
            $lists['filter'] = HTMLHelper::_('select.genericlist', $filters, 'filter', array('size' => '1', 'class' => 'form-select'), 'value', 'text', $filter);

            // search filter
            $lists['search'] = $search;

            // table ordering
            $lists['order_Dir'] = $filter_order_Dir;
            $lists['order'] = $filter_order;

            // pathway
            if ($menuitem) {
                $pathwayKeys = array_keys($pathway->getPathway());
                $lastPathwayEntryIndex = end($pathwayKeys);
                $pathway->setItemName($lastPathwayEntryIndex, $menuitem->title);
                //$pathway->setItemName(1, $menuitem->title);
            }

            // Set Page title
            $pagetitle = Text::_('com_planjeagenda_MY_VENUES');
            $pageheading = $pagetitle;
            $pageclass_sfx = '';

            // Check to see which parameters should take priority
            if ($useMenuItemParams) {
                // Menu item params take priority
                $params->def('page_title', $menuitem->title);
                $pagetitle = $params->get('page_title', Text::_('com_planjeagenda_MY_VENUES'));
                $pageheading = $params->get('page_heading', $pagetitle);
                $pageclass_sfx = $params->get('pageclass_sfx');
            }

            // ($task == 'archive') useless
            $print_link = Route::_(\PlanjeagendaHelperRoute::getMyVenuesRoute() . '&print=1&tmpl=component');

            $params->set('page_heading', $pageheading);

            // Add site name to title if param is set
            if ($app->get('sitename_pagetitles', 0) == 1) {
                $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
            } elseif ($app->get('sitename_pagetitles', 0) == 2) {
                $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
            }

            $document->setTitle($pagetitle);
            $document->setMetaData('title', $pagetitle);

            // Should we show publish buttons?
            $canPublishVenue = false;
            foreach ($venues as $venue) {
                $canPublishVenue |= $venue->params->get('access-change');
                if ($canPublishVenue) break;
            }

            //Check if the user has permission to add things
            $permissions = new \stdClass();
            $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
            $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');
            $permissions->canPublishVenue = $canPublishVenue;

            $this->action = $uri->toString();
            $this->venues         = $venues;
            $this->task         = $task;
            $this->print         = $print;
            $this->params         = $params;
            $this->pagination     = $pagination;
            $this->jemsettings     = $jemsettings;
            $this->settings     = $settings;
            $this->pagetitle     = $pagetitle;
            $this->lists         = $lists;
            $this->novenues     = $novenues;
            $this->permissions     = $permissions;
            $this->print_link = $print_link;
            $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;
        }
        parent::display($tpl);
    }
}
