<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Attendees;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Attendees-view
 * @todo fix view
 */
class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        $app  = Factory::getApplication();
        $user = Factory::getApplication()->getIdentity();

        //redirect if not logged in
        if (!$user->id) {
            $app->enqueueMessage(Text::_('com_planjeagenda_NEED_LOGGED_IN'), 'error');
            return false;
        }

        $this->settings    = \PlanjeagendaHelper::globalattribs();
        $this->jemsettings = \PlanjeagendaHelper::config();

        if ($this->getLayout() == 'print') {
            $this->_displayprint($tpl);
            return;
        }

        if ($this->getLayout() == 'addusers') {
            $this->returnto = base64_decode($app->input->get('return', '', 'base64'));
            $this->_displayaddusers($tpl);
            return;
        }

        //initialise variables
        $document   = $app->getDocument();
        $settings    = $this->settings;
        $params     = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
        $menu        = $app->getMenu();
        $menuitem    = $menu->getActive();
        $uri        = Uri::getInstance();

        // Load css
        \PlanjeagendaHelper::loadCss('klevents');
        \PlanjeagendaHelper::loadCustomCss();
        \PlanjeagendaHelper::loadCustomTag();

        //get vars
        $filter_order        = $app->getUserStateFromRequest('com_planjeagenda.attendees.filter_order', 'filter_order', 'u.username', 'cmd');
        $filter_order_Dir    = $app->getUserStateFromRequest('com_planjeagenda.attendees.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $filter_status        = $app->getUserStateFromRequest('com_planjeagenda.attendees.filter_status', 'filter_status', -2, 'int');
        $filter             = $app->getUserStateFromRequest('com_planjeagenda.attendees.filter', 'filter', 0, 'int');
        $search             = $app->getUserStateFromRequest('com_planjeagenda.attendees.filter_search', 'filter_search', '', 'string');

        // Get data from the model
        $rows          = $this->get('Data');
        $pagination = $this->get('Pagination');
        $event         = $this->get('Event');

        // Merge params.
        // Because this view is not useable for menu item we always overwrite $params.
        $pagetitle = Text::_('com_planjeagenda_MYEVENT_MANAGEATTENDEES') . ' - ' . $event->title;
        $params->set('page_heading', Text::_('com_planjeagenda_MYEVENT_MANAGEATTENDEES')); // event title is shown separate
        //$params->set('show_page_heading', 1); // always show?
        $params->set('introtext', ''); // there can't be an introtext
        $params->set('showintrotext', 0);
        $pageclass_sfx = $params->get('pageclass_sfx');

        // Add site name to title if param is set
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
        }

        $document->setTitle($pagetitle);

        $pathway = $app->getPathWay();
        if($menuitem) {
      //https://www.koelmanlabs.nl/forum/klevents-2-2-x-on-joomla-3/10474-category-name-doubled-in-breadcrumb
      $pathwayKeys = array_keys($pathway->getPathway()); //
      $lastPathwayEntryIndex = end($pathwayKeys);
      $pathway->setItemName($lastPathwayEntryIndex, $menuitem->title);
      //$pathway->setItemName(1, $menuitem->title);
        }
        $pathway->addItem('Att:'.$event->title);

        // Emailaddress
        $enableemailaddress = $params->get('enableemailaddress', 0);

        $print_link = 'index.php?option=com_planjeagenda&view=attendees&layout=print&task=print&tmpl=component&id='.$event->id;
        $backlink = 'attendees';


        //build filter selectlist
        $filters = array();
        if ($settings->get('global_regname', '1')) {
            $filters[] = HTMLHelper::_('select.option', '1', Text::_('com_planjeagenda_NAME'));
        } else {
            $filters[] = HTMLHelper::_('select.option', '2', Text::_('com_planjeagenda_USERNAME'));
        }
        $lists['filter'] = HTMLHelper::_('select.genericlist', $filters, 'filter', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter);

        // search filter
        $lists['search'] = $search;

        // attendee status
        $options = array(HTMLHelper::_('select.option', -2, Text::_('com_planjeagenda_ATT_FILTER_ALL')),
                         HTMLHelper::_('select.option',  0, Text::_('com_planjeagenda_ATT_FILTER_INVITED')),
                         HTMLHelper::_('select.option', -1, Text::_('com_planjeagenda_ATT_FILTER_NOT_ATTENDING')),
                         HTMLHelper::_('select.option',  1, Text::_('com_planjeagenda_ATT_FILTER_ATTENDING')),
                         HTMLHelper::_('select.option',  2, Text::_('com_planjeagenda_ATT_FILTER_WAITING'))) ;
        $lists['status'] = HTMLHelper::_('select.genericlist', $options, 'filter_status', array('class'=>'inputbox','onChange'=>'this.form.submit();'), 'value', 'text', $filter_status);

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']        = $filter_order;

        //assign to template
        $this->params        = $params;
        $this->lists         = $lists;
        $this->enableemailaddress = $enableemailaddress;
        $this->rows         = $rows;
        $this->pagination     = $pagination;
        $this->event         = $event;
        $this->pagetitle    = $pagetitle;
        $this->backlink        = $backlink;
        $this->print_link    = $print_link;
        $this->item            = $menuitem;
        $this->action        = $uri->toString();
        $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;


        parent::display($tpl);
    }

    /**
     * Prepares the print screen
     *
     * @param $tpl
     */
    protected function _displayprint($tpl = null)
    {
        $app = Factory::getApplication();
        $document = $app->getDocument();
        $params        = $app->getParams();

        // Load css
        \PlanjeagendaHelper::loadCss('backend');
        \PlanjeagendaHelper::loadCss('klevents');
        \PlanjeagendaHelper::loadCss('print');
        \PlanjeagendaHelper::loadCustomTag();

        $document->setMetaData('robots', 'noindex, nofollow');

        // Emailaddress
        $enableemailaddress = $params->get('enableemailaddress', 0);

        $rows      = $this->get('Data');
        $event     = $this->get('Event');

        //assign data to template
        $this->rows         = $rows;
        $this->event         = $event;
        $this->enableemailaddress = $enableemailaddress;

        parent::display($tpl);
    }

    /**
     * Creates the output for the users select listing
     */
    protected function _displayaddusers($tpl)
    {
        $app         = Factory::getApplication();
        $document    = $app->getDocument();
        $jinput      = $app->input;
    //    $db          = Factory::getContainer()->get('DatabaseDriver');
        $model       = $this->getModel();
        $event       = $this->get('Event');

        // no filters, hard-coded
        $filter_order     = 'usr.name';
        $filter_order_Dir = '';
        $filter_type      = '';
        $search           = $app->getUserStateFromRequest('com_planjeagenda.selectusers.filter_search', 'filter_search', '', 'string');
    //    $limitstart       = $jinput->get('limitstart', '0', 'int');
    //    $limit            = $app->getUserStateFromRequest('com_planjeagenda.selectusers.limit', 'limit', $this->jemsettings->display_num, 'int');
    //    $eventId          = !empty($event->id) ? $event->id : 0;

        // Load css
        \PlanjeagendaHelper::loadCss('klevents');

        $document->setTitle(Text::_('com_planjeagenda_SELECT_USERS_AND_STATUS'));

        // Get/Create the model
    //    $model->setState('event.id', $eventId);
        $rows       = $this->get('Users');
        $pagination = $this->get('UsersPagination');

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        //Build search filter - unused
        $filters = array();
        $filters[] = HTMLHelper::_('select.option', '1', Text::_('com_planjeagenda_NAME'));
        $searchfilter = HTMLHelper::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

        // search filter - unused
        $lists['search'] = $search;

        //assign data to template
        $this->searchfilter = $searchfilter;
        $this->lists        = $lists;
        $this->rows         = $rows;
        $this->pagination   = $pagination;
        $this->event        = $event;

        parent::display($tpl);
    }
}
