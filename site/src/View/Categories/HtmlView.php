<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Categories;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
/**
 * Categories-View
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Creates the Categories-View
     */
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        $app = Factory::getApplication();

        $document    = $app->getDocument();
        $jemsettings = \PlanjeagendaHelper::config();
        $user        = Factory::getApplication()->getIdentity();
        $print       = $app->input->getBool('print', false);
        $task        = $app->input->getCmd('task', '');
        $id          = $app->input->getInt('id', 1);
        $uri         = Uri::getInstance();
        // Load frontend categories model directly to avoid admin model conflict
        if (!class_exists('PlanjeagendaModelCategoriesFrontend')) {
            require_once JPATH_SITE . '/components/com_planjeagenda/models/categories.php';
        }
        $frontendModel = new PlanjeagendaModelCategoriesFrontend();
        $model         = $frontendModel;
        $rows          = $frontendModel->getData();
        $pagination  = $frontendModel->getPagination();

        // Load css
        \PlanjeagendaHelper::loadCss('klevents');
        \PlanjeagendaHelper::loadCustomCss();
        \PlanjeagendaHelper::loadCustomTag();

        if ($print) {
            \PlanjeagendaHelper::loadCss('print');
            $document->setMetaData('robots', 'noindex, nofollow');
        }

        // get menu information
        $menu          = $app->getMenu();
        $menuitem      = $menu->getActive();
        $params        = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams('com_planjeagenda'));

        $pagetitle     = $params->def('page_title', $menuitem->title);
        $pageheading   = $params->def('page_heading', $params->get('page_title'));
        $pageclass_sfx = $params->get('pageclass_sfx');

        // pathway
        $pathway = $app->getPathWay();
        if ($menuitem) {
            $pathwayKeys = array_keys($pathway->getPathway());
            $lastPathwayEntryIndex = end($pathwayKeys);
            $pathway->setItemName($lastPathwayEntryIndex, $menuitem->title);
            //$pathway->setItemName(1, $menuitem->title);
        }

        if ($task == 'archive') {
            $pathway->addItem(Text::_('com_planjeagenda_ARCHIVE'), Route::_('index.php?option=com_planjeagenda&view=categories&id='.$id.'&task=archive'));
            $print_link = Route::_('index.php?option=com_planjeagenda&view=categories&id='.$id.'&task=archive&print=1&tmpl=component');
            $pagetitle   .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
            $pageheading .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
            $archive_link = Route::_('index.php?option=com_planjeagenda&view=categories');
            $params->set('page_heading', $pageheading);
        } else {
            $print_link = Route::_('index.php?option=com_planjeagenda&view=categories&id='.$id.'&print=1&tmpl=component');
            $archive_link = $uri->toString();
        }

        // Add site name to title if param is set
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
        }

        // Set Page title
        $document->setTitle($pagetitle);
        $document->setMetaData('title' , $pagetitle);

        // Check if the user has permission to add things
        $permissions = new \stdClass();
        $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
        $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');

        // Get events if requested
        if (!empty($rows) && $params->get('detcat_nr', 0) > 0) {
            foreach($rows as $row) {
                $row->events = $frontendModel->getEventdata($row->id);
            }
        }

        $this->rows          = $rows;
        $this->task          = $task;
        $this->params        = $params;
        $this->dellink       = $permissions->canAddEvent; // deprecated
        $this->pagination    = $pagination;
        $this->item          = $menuitem;
        $this->jemsettings   = $jemsettings;
        $this->pagetitle     = $pagetitle;
        $this->print_link    = $print_link;
        $this->archive_link  = $archive_link;
        $this->model         = $model;
        $this->id            = $id;
        $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;
        $this->permissions   = $permissions;

        parent::display($tpl);
    }
}
