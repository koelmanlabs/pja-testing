<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Myattendances;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
/**
 * Myattendances-View
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Creates the Myattendances View
     */
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        // initialize variables
        $app         = Factory::getApplication();
        $document    = $app->getDocument();
        $jemsettings = \PlanjeagendaHelper::config();
        $settings    = \PlanjeagendaHelper::globalattribs();
        $menu        = $app->getMenu();
        $menuitem    = $menu->getActive();
        $uri         = Uri::getInstance();
        $params      = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
        $uri         = Uri::getInstance();
        $user        = Factory::getApplication()->getIdentity();
        $pathway     = $app->getPathWay();
        $print       = $app->input->getBool('print', false);
        $task        = $app->input->getCmd('task', '');

        // redirect if not logged in
        $this->needLoginFirst = 0;
        if (!$user->id) {
            $app->enqueueMessage(Text::_('com_planjeagenda_NEED_LOGGED_IN'), 'error');
            $this->needLoginFirst=1;
        }else {
            // Decide which parameters should take priority
            $useMenuItemParams = ($menuitem && $menuitem->query['option'] == 'com_planjeagenda'
                && $menuitem->query['view'] == 'myattendances');

            // Load css
            \PlanjeagendaHelper::loadCss('klevents');
            \PlanjeagendaHelper::loadCustomCss();
            \PlanjeagendaHelper::loadCustomTag();

            if ($print) {
                \PlanjeagendaHelper::loadCss('print');
                $document->setMetaData('robots', 'noindex, nofollow');
            }

            $attending = $this->get('Attending');
            $attending_pagination = $this->get('AttendingPagination');

            // are attendences available?
            $noattending = (!$attending) ? 1 : 0;

            // get variables
            $filter_order = $app->getUserStateFromRequest('com_planjeagenda.myattendances.filter_order', 'filter_order', 'a.dates', 'cmd');
            $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.myattendances.filter_order_Dir', 'filter_order_Dir', '', 'word');
//         $filter_state     = $app->getUserStateFromRequest('com_planjeagenda.myattendances.filter_state',     'filter_state',     '*',      'word');
            $filter = $app->getUserStateFromRequest('com_planjeagenda.myattendances.filter', 'filter', 0, 'int');
            $search = $app->getUserStateFromRequest('com_planjeagenda.myattendances.filter_search', 'filter_search', '', 'string');

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
            $pagetitle = Text::_('com_planjeagenda_MY_ATTENDANCES');
            $pageheading = $pagetitle;

            // Check to see which parameters should take priority
            if ($useMenuItemParams) {
                // Menu item params take priority
                $params->def('page_title', $menuitem->title);
                $pagetitle = $params->get('page_title', Text::_('com_planjeagenda_MY_ATTENDANCES'));
                $pageheading = $params->get('page_heading', $pagetitle);
                $pageclass_sfx = $params->get('pageclass_sfx');
            }

            if ($task == 'archive') {
                $pathway->addItem(Text::_('com_planjeagenda_ARCHIVE'), Route::_(\PlanjeagendaHelperRoute::getMyAttendancesRoute() . '&task=archive'));
                $print_link = Route::_(\PlanjeagendaHelperRoute::getMyAttendancesRoute() . '&task=archive&print=1&tmpl=component');
                $pagetitle .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
                $pageheading .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
                $archive_link = Route::_('index.php?option=com_planjeagenda&view=myattendances');
                $params->set('page_heading', $pageheading);
            } else {
                $print_link = Route::_(\PlanjeagendaHelperRoute::getMyAttendancesRoute() . '&print=1&tmpl=component');
                $archive_link = $uri->toString();
            }

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
            $document->setMetaData('title', $pagetitle);

            // Don't add things from this view, no good starting point
            $permissions = new \stdClass();

            $this->action = $uri->toString();
            $this->attending = $attending;
            $this->task = $task;
            $this->params = $params;
            $this->attending_pagination = $attending_pagination;
            $this->jemsettings = $jemsettings;
            $this->settings = $settings;
            $this->permissions = $permissions;
            $this->pagetitle = $pagetitle;
            $this->print_link = $print_link;
            $this->archive_link = $archive_link;
            $this->print = $print;
            $this->lists = $lists;
            $this->noattending = $noattending;
            $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;

        }
        parent::display($tpl);
    }
}
