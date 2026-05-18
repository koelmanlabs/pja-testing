<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Editevent;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;
/**
 * Editevent-View
 */
class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $return_page;
    protected $state;

    /**
     * Editevent-View
     */
    public function display($tpl = null)
    {
        
        if (!class_exists('PlanjeagendaImage')) {
            require_once JPATH_SITE . '/components/com_planjeagenda/classes/image.class.php';
        }
        
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        if ($this->getLayout() == 'choosevenue') {
            $this->_displaychoosevenue($tpl);
            return;
        }

        if ($this->getLayout() == 'choosecontact') {
            $this->_displaychoosecontact($tpl);
            return;
        }

        if ($this->getLayout() == 'chooseusers') {
            $this->_displaychooseusers($tpl);
            return;
        }

        // Initialise variables.
        $jemsettings = PlanjeagendaHelper::config();
        $settings    = PlanjeagendaHelper::globalattribs();
        $app         = Factory::getApplication();
        $user        = Factory::getApplication()->getIdentity();
        $userId      = $user->id;
        $document    = $app->getDocument();
        $menu        = $app->getMenu();
        $menuitem    = $menu->getActive();
        $pathway     = $app->getPathway();
        $url         = Uri::root();
        
        // Get model data.
        
        $model = $app->bootComponent('com_planjeagenda')
        ->getMVCFactory()
        ->createModel('Form', 'Site', ['ignore_request' => false]);
        
        if (!$model) {
            throw new \RuntimeException('Eventslist model not found');
        }
        $this->state       = $model->getState();
        $this->item        = $model->getItem();
        $this->form        = $model->getForm();
        $this->return_page = $model->getReturnPage();
        $this->params = $this->state?->get('params');
            
        // Create a shortcut for $item and params.
        $item = $this->item;
        $params = $this->params;

        $this->return_page = $this->get('ReturnPage');
        $this->invited = (array)$this->get('InvitedUsers');

        // Check for data error
        if (empty($item)) {
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }

        // Must be logged in
        if ($userId == 0) {
            $app->enqueueMessage('Not logged in', 'error');
            return false;
        }

        // Check edit permission (set by model using component settings)
        $canEdit = (bool)($item->params?->get('access-edit', false));
        if (!$canEdit) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            return false;
        }

        // Decide which parameters should take priority
        $useMenuItemParams = ($menuitem && $menuitem->query['option'] == 'com_planjeagenda'
            && ($menuitem->query['view']   == 'editevent')
            && (0 == $item->id) && (!Factory::getApplication()->input->getInt('from_id', 0))); // menu item is always for new event

        $title = ($item->id == 0) ? Text::_('com_planjeagenda_EDITEVENT_ADD_EVENT')
            : Text::sprintf('com_planjeagenda_EDITEVENT_EDIT_EVENT', $item->title);

        if ($useMenuItemParams) {
            $pagetitle = $menuitem->title ? $menuitem->title : $title;
            $params->def('page_title', $pagetitle);
            $params->def('page_heading', $pagetitle);
            $pathwayKeys = array_keys($pathway->getPathway());
            $lastPathwayEntryIndex = end($pathwayKeys);
            $pathway->setItemName($lastPathwayEntryIndex, $menuitem->title);
            //$pathway->setItemName(1, $menuitem->title);

            // Load layout from menu item if one is set else from event if there is one set
            if (isset($menuitem->query['layout'])) {
                $this->setLayout($menuitem->query['layout']);
            } elseif ($layout = $item->params->get('event_layout')) {
                $this->setLayout($layout);
            }

            $item->params->merge($params);
        } else {
            $pagetitle = $title;

            $params->set('page_title', $pagetitle);
            $params->set('page_heading', $pagetitle);
            $params->set('show_page_heading', 1); // ensure page heading is shown
            $params->set('introtext', ''); // there is definitely no introtext.
            $params->set('showintrotext', 0);
            $pathway->addItem($pagetitle, ''); // link not required here so '' is ok

            // Check for alternative layouts (since we are not in an edit-event menu item)
            // Load layout from event if one is set
            if ($layout = $item->params->get('event_layout')) {
                $this->setLayout($layout);
            }

            $temp = clone($params);
            $temp->merge($item->params);
            $item->params = $temp;
        }

        if (!empty($this->item) && isset($this->item->id)) {
            // $this->item->images = json_decode($this->item->images);
            // $this->item->urls = json_decode($this->item->urls);

            $tmp = new \stdClass();

            // check for recurrence
            if (($this->item->recurrence_type != 0) || ($this->item->recurrence_first_id != 0)) {
                $tmp->recurrence_type = 0;
                $tmp->recurrence_first_id = 0;
            }

            // $tmp->images = $this->item->images;
            // $tmp->urls = $this->item->urls;
          //   $this->form->bind($tmp);
        }

        if (empty($item->id)) {
            if (!empty($item->catid)) {
                $this->form->setFieldAttribute('cats', 'prefer', $item->catid);
            }
            if (!empty($item->locid)) {
                $tmp = new \stdClass();
                $tmp->locid = $item->locid;
                $this->form->bind($tmp);
            }
        }

        // Check for errors.
        $errors = $this->get('Errors');
        if (is_array($errors) && count($errors)) {
            Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'warning');
            return false;
        }

        // Get event access level for the access dropdown
 //       $access  = isset($item->access) ? (int)$item->access : 1;
//        $access2 = PlanjeagendaHelper::getAccesslevelOptions(true, $access);
//        $this->access = $access2;

        // Load css
        PlanjeagendaHelper::loadCss('klevents');
        PlanjeagendaHelper::loadCustomCss();

        // Load scripts
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerScript('klevents.attachments', 'com_planjeagenda/attachments.js')->useScript('klevents.attachments');
        $wa->registerScript('klevents.recurrence', 'com_planjeagenda/recurrence.js')->useScript('klevents.recurrence');
        $wa->registerScript('klevents.seo', 'com_planjeagenda/seo.js')->useScript('klevents.seo');
        $wa->registerScript('klevents.unlimited', 'com_planjeagenda/unlimited.js')->useScript('klevents.unlimited');
        $wa->registerScript('klevents.other', 'com_planjeagenda/other.js')->useScript('klevents.other');

        // Escape strings for HTML output
        $pageclass_sfx          = $item->params->get('pageclass_sfx');
        $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;
        $this->dimage        = \PlanjeagendaImage::flyercreator($this->item->datimage, 'event');
        $this->jemsettings   = $jemsettings;
        $this->settings      = $settings;
        $this->infoimage     = HTMLHelper::_('image', 'com_planjeagenda/icon-16-hint.webp', Text::_('com_planjeagenda_NOTES'), NULL, true);

        $this->user = $user;
        $permissions = new \stdClass();
        $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');
        $this->permissions = $permissions;

        if ($params->get('enable_category') == 1) {
            $this->form->setFieldAttribute('catid', 'default', $params->get('catid', 1));
            $this->form->setFieldAttribute('catid', 'readonly', 'true');
        }

        // disable for non-publishers
        if (empty($item->params) || !$item->params->get('access-change', false)) {
            $this->form->setFieldAttribute('published', 'default', 0);
            $this->form->setFieldAttribute('published', 'readonly', 'true');
        }

        // configure image field: show max. file size, and possibly mark field as required
        $tip = Text::_('com_planjeagenda_UPLOAD_IMAGE');
        if ((int)$jemsettings->sizelimit > 0) {
            $tip .= ' <br>' . Text::sprintf('com_planjeagenda_MAX_FILE_SIZE_1', (int)$jemsettings->sizelimit);
        }
 //       $this->form->setFieldAttribute('userfile', 'description', $tip);
        if ($jemsettings->imageenabled == 2) {
            $this->form->setFieldAttribute('userfile', 'required', 'true');
        }

        // configure invited field
        if ($jemsettings->regallowinvitation == 1) {
 //           $this->form->setValue('invited', null, implode(',', $this->invited));
 //           $this->form->setFieldAttribute('invited', 'eventid', (int)$this->item->id);
        }

        $this->_prepareDocument();
        parent::display($tpl);
    }


    /**
     * Prepares the document
     */
    protected function _prepareDocument()
    {
        $document = \Joomla\CMS\Factory::getApplication()->getDocument();
        $app = Factory::getApplication();

        $title = $this->params->get('page_title');
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }
        $document->setTitle($title);

        // TODO: Is it useful to have meta data in an edit view?
        //       Also shouldn't be "robots" set to "noindex, nofollow"?
        if ($this->params->get('menu-meta_description')) {
            $document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $document->setMetadata('robots', $this->params->get('robots'));
        }
    }


    /**
     * Creates the output for the venue select listing
     */
    protected function _displaychoosevenue($tpl)
    {
        $app         = Factory::getApplication();
        $jinput      = Factory::getApplication()->input;
        $jemsettings = \PlanjeagendaHelper::config();
        //    $db          = Factory::getContainer()->get('DatabaseDriver');
        $document    = $app->getDocument();

        $filter_order     = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_order', 'filter_order', 'l.venue', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');
        $filter_type      = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_type', 'filter_type', 0, 'int');
        $filter_state     = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_state', 'filter_state', '*', 'word');
        $search           = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.filter_search', 'filter_search', '', 'string');
        $limitstart       = $jinput->get('limitstart', '0', 'int');
        $limit            = $app->getUserStateFromRequest('com_planjeagenda.selectvenue.limit', 'limit', $jemsettings->display_num, 'int');

        // Get/Create the model
        $rows       = $this->get('Venues');
        $pagination = $this->get('VenuesPagination');

        // filter state
        $lists['state'] = HTMLHelper::_('grid.state', $filter_state);

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        $document->setTitle(Text::_('com_planjeagenda_SELECT_VENUE'));
        \PlanjeagendaHelper::loadCss('klevents');

        $filters = array();
        $filters[] = HTMLHelper::_('select.option', '1', Text::_('com_planjeagenda_VENUE'));
        $filters[] = HTMLHelper::_('select.option', '2', Text::_('com_planjeagenda_CITY'));
        $filters[] = HTMLHelper::_('select.option', '3', Text::_('com_planjeagenda_STATE'));
        $searchfilter = HTMLHelper::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

        $this->rows         = $rows;
        $this->searchfilter = $searchfilter;
        $this->pagination   = $pagination;
        $this->lists        = $lists;
        $this->filter       = $search;

        parent::display($tpl);
    }


    /**
     * Creates the output for the contact select listing
     */
    protected function _displaychoosecontact($tpl)
    {
        $app         = Factory::getApplication();
        $jinput      = Factory::getApplication()->input;
        $jemsettings = \PlanjeagendaHelper::config();
        //    $db          = Factory::getContainer()->get('DatabaseDriver');
        $document    = $app->getDocument();

        $filter_order     = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.filter_order', 'filter_order', 'con.name', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $filter_type      = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.filter_type', 'filter_type', 0, 'int');
        $search           = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.filter_search', 'filter_search', '', 'string');
        $limitstart       = $jinput->get('limitstart', '0', 'int');
        $limit            = $app->getUserStateFromRequest('com_planjeagenda.selectcontact.limit', 'limit', $jemsettings->display_num, 'int');

        // Load css
        \PlanjeagendaHelper::loadCss('klevents');

        $document->setTitle(Text::_('com_planjeagenda_SELECT_CONTACT'));

        // Get/Create the model
        $rows       = $this->get('Contacts');
        $pagination = $this->get('ContactsPagination');

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        //Build search filter
        $filters = array();
        $filters[] = HTMLHelper::_('select.option', '1', Text::_('com_planjeagenda_NAME'));
        /*    $filters[] = HTMLHelper::_('select.option', '2', Text::_('com_planjeagenda_ADDRESS')); */ // data security
        $filters[] = HTMLHelper::_('select.option', '3', Text::_('com_planjeagenda_CITY'));
        $filters[] = HTMLHelper::_('select.option', '4', Text::_('com_planjeagenda_STATE'));
        $searchfilter = HTMLHelper::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

        // search filter
        $lists['search']= $search;

        //assign data to template
        $this->searchfilter = $searchfilter;
        $this->lists        = $lists;
        $this->rows         = $rows;
        $this->pagination   = $pagination;

        parent::display($tpl);
    }


    /**
     * Creates the output for the users select listing
     */
    protected function _displaychooseusers($tpl)
    {
        $app         = Factory::getApplication();
        $jinput      = $app->input;
        $jemsettings = \PlanjeagendaHelper::config();
        //    $db          = Factory::getContainer()->get('DatabaseDriver');
        $document    = $app->getDocument();
        $model       = $this->getModel();

        // no filters, hard-coded
        $filter_order     = 'usr.name';
        $filter_order_Dir = '';
        $filter_type      = '';
        $search           = '';
        $limitstart       = 0;
        $limit            = 0;
        $eventId          = $jinput->getInt('a_id', 0);

        // Load css
        \PlanjeagendaHelper::loadCss('klevents');

        $document->setTitle(Text::_('com_planjeagenda_SELECT_USERS_TO_INVITE'));

        // Get/Create the model
        $model->setState('event.id', $eventId);
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
        $lists['search']= $search;

        //assign data to template
        $this->searchfilter = $searchfilter;
        $this->lists        = $lists;
        $this->rows         = $rows;
        $this->pagination   = $pagination;

        parent::display($tpl);
    }

}
