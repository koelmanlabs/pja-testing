<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Venues;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Venues-View
*/
class HtmlView extends BaseHtmlView
{
    /**
     * Creates the Venuesview
     */
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        $app         = Factory::getApplication();
        $document    = $app->getDocument();
        $jemsettings = \PlanjeagendaHelper::config();
        $settings    = \PlanjeagendaHelper::globalattribs();
        $user        = Factory::getApplication()->getIdentity();
        $jinput      = $app->input;
        $print       = $jinput->getBool('print', false);
        $task        = $jinput->getCmd('task', '');

        //get menu information
        $menu     = $app->getMenu();
        $menuitem = $menu->getActive();
        $params   = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
        $model    = $this->getModel();

        // Load css
        \PlanjeagendaHelper::loadCss('klevents');
        \PlanjeagendaHelper::loadCustomCss();
        \PlanjeagendaHelper::loadCustomTag();

        if ($print) {
            \PlanjeagendaHelper::loadCss('print');
            $document->setMetaData('robots', 'noindex, nofollow');
        }

        // Request variables
        $items = $this->get('Items');

        foreach ($items AS $item) {
            // Create image information
            $item->limage = \PlanjeagendaImage::flyercreator($item->locimage, 'venue');

            // Generate Venuedescription
            if (!$item->locdescription == '' || !$item->locdescription == '<br>') {
                //execute plugins
                $item->text = $item->locdescription;
                $item->title = $item->venue;
                PluginHelper::importPlugin('content');
                $app->triggerEvent('onContentPrepare', array('com_planjeagenda.venue', &$item, &$params, 0));
                $item->locdescription = $item->text;
            }

            //build the url
            if (!empty($item->url) && !preg_match('%^http(s)?://%', $item->url)) {
                $item->url = 'https://'.$item->url;
            }

            //create target link
            $item->linkEventsArchived = Route::_(\PlanjeagendaHelperRoute::getVenueRoute($item->venueslug.'&task=archive'));
            $item->linkEventsPublished = Route::_(\PlanjeagendaHelperRoute::getVenueRoute($item->venueslug));

            $item->EventsPublished = $model->AssignedEvents($item->locid,"1");
            $item->EventsArchived = $model->AssignedEvents($item->locid,"2");
        }

        $pagetitle = $params->def('page_title', $menuitem->title);
        $pageheading = $params->def('page_heading', $params->get('page_title'));
        $pageclass_sfx = $params->get('pageclass_sfx');

        //pathway
        $pathway = $app->getPathWay();
    $pathwayKeys = array_keys($pathway->getPathway());
    $lastPathwayEntryIndex = end($pathwayKeys);
    $pathway->setItemName($lastPathwayEntryIndex, $menuitem->title);
    //$pathway->setItemName(1, $menuitem->title);

        if ($task == 'archive') {
            $pathway->addItem(Text::_('com_planjeagenda_ARCHIVE'), Route::_('index.php?option=com_planjeagenda&view=venues&task=archive'));
            $print_link = Route::_('index.php?option=com_planjeagenda&view=venues&task=archive&print=1&tmpl=component');
            $pagetitle   .= ' - '.Text::_('com_planjeagenda_ARCHIVE');
            $pageheading .= ' - '.Text::_('com_planjeagenda_ARCHIVE');
            $params->set('page_heading', $pageheading);
        } else {
            $print_link = Route::_('index.php?option=com_planjeagenda&view=venues&print=1&tmpl=component');
        }

        // Add site name to title if param is set
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
        }

        //Set Page title
        $document->setTitle($pagetitle);
        $document->setMetadata('title', $pagetitle);
        $document->setMetadata('keywords', $pagetitle);

        //Check if the user has permission to add things
        $permissions = new \stdClass();
        $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
        $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');
        $permissions->canEditPublishVenue = $user->authorise('core.edit.state', 'com_planjeagenda');

        // Create the pagination object
        $pagination = $this->get('Pagination');

        $this->rows          = $items;
        $this->print_link    = $print_link;
        $this->params        = $params;
        $this->pagination    = $pagination;
        $this->item          = $menuitem;
        $this->jemsettings   = $jemsettings;
        $this->settings      = $settings;
        $this->permissions   = $permissions;
        $this->show_status   = $permissions->canEditPublishVenue;
        $this->task          = $task;
        $this->pagetitle     = $pagetitle;
        $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;

        parent::display($tpl);
    }
}
