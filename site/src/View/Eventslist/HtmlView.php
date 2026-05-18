<?php

/**
 * @package Planjeagenda
 * @copyright (C) 2026 Koelman Labs
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Eventslist;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;

/**
 * Eventslist View
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $jemsettings = PlanjeagendaHelper::config();
        $settings = PlanjeagendaHelper::globalattribs();

        $menu = $app->getMenu();
        $menuitem = $menu->getActive();
        $document = $app->getDocument();

        $params = $app->isClient('administrator')
            ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda')
            : $app->getParams();

        $uri = Uri::getInstance();
        $jinput = $app->input;
        $task = $jinput->getCmd('task', '');
        $print = $jinput->getBool('print', false);

        $user = $app->getIdentity();

        // === VEILIGHEID: Menu params correct ophalen (Joomla 6) ===
        $menuParams = $menuitem ? $menuitem->getParams() : new Registry();

        $itemid = $jinput->getInt('id', 0) . ':' . $jinput->getInt('Itemid', 0);

        // === MONTH PICKER FALLBACK ===
        if (method_exists($document, 'getWebAssetManager')) {
            $wa = $document->getWebAssetManager();

            if (!$wa->assetExists('script', 'com_planjeagenda.monthpicker')) {
                $wa->registerScript(
                    'com_planjeagenda.monthpicker',
                    'media/com_planjeagenda/js/monthpicker-fallback.js',
                    [
                        'version' => 'auto',
                        'defer'   => true
                    ]
                );
            }
            $wa->useScript('com_planjeagenda.monthpicker');
        }

        // Print mode
        if ($print) {
            $document->setMetaData('robots', 'noindex, nofollow');
        }

        // === USER STATE & FILTERS OPHALEN (Hersteld uit oude view) ===
        $filter_type = $app->getUserStateFromRequest(
            'com_planjeagenda.eventslist.' . $itemid . '.filter_type',
            'filter_type',
            0,
            'int'
        );
        $search = $app->getUserStateFromRequest(
            'com_planjeagenda.eventslist.' . $itemid . '.filter_search',
            'filter_search',
            '',
            'string'
        );
        $search_month = $app->getUserStateFromRequest(
            'com_planjeagenda.eventslist.' . $itemid . '.filter_month',
            'filter_month',
            '',
            'string'
        );

        // Filter alleen featured events indien ingesteld in menu-opties
        if ($params->get('onlyfeatured')) {
            $this->getModel()->setState('filter.featured', 1);
        }

        // === SORTEERVOLGORDE BEPALEN ===
        $tableInitialorderby = $params->get('tableorderby', '0');
        if ($tableInitialorderby) {
            switch ($tableInitialorderby) {
                case 0: $tableInitialorderby = 'a.dates'; break;
                case 1: $tableInitialorderby = 'a.title'; break;
                case 2: $tableInitialorderby = 'l.venue'; break;
                case 3: $tableInitialorderby = 'l.city'; break;
                case 4: $tableInitialorderby = 'l.state'; break;
                case 5: $tableInitialorderby = 'c.catname'; break;
            }
            $filter_order = $app->getUserStateFromRequest(
                'com_planjeagenda.eventslist.' . $itemid . '.filter_order',
                'filter_order',
                $tableInitialorderby,
                'cmd'
            );
        } else {
            $filter_order = $app->getUserStateFromRequest(
                'com_planjeagenda.eventslist.' . $itemid . '.filter_order',
                'filter_order',
                'a.dates',
                'cmd'
            );
        }

        $tableInitialDirectionOrder = $params->get('tabledirectionorder', 'ASC');
        $filter_order_Dir = $app->getUserStateFromRequest(
            'com_planjeagenda.eventslist.' . $itemid . '.filter_order_Dir',
            'filter_order_Dir',
            $tableInitialDirectionOrder ?: 'ASC',
            'word'
        );

        // Omdraaien van standaardvolgorde in archiefmodus
        if ($task === 'archive' && $filter_order === 'a.dates') {
            $filter_order_Dir = 'DESC';
        }

        // === BOUW DE FILTER SELECTIELIJST OP ===
        $lists = [];
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        $filters = [];
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

        $lists['filter'] = HTMLHelper::_(
            'select.genericlist',
            $filters,
            'filter_type',
            ['size' => '1', 'class' => 'form-select'],
            'value',
            'text',
            $filter_type
        );
        $lists['search'] = $search;
        $lists['month']  = $search_month;

        // Get data from model
        $rows = $this->get('Items');
        $noevents = empty($rows);

        // Page titles
        $pagetitle = $params->def('page_title', $menuitem ? $menuitem->title : Text::_('com_planjeagenda_EVENTS'));
        $pageheading = $params->def('page_heading', $params->get('page_title'));
        $pageclass_sfx = $params->get('pageclass_sfx', '');

        // Pathway
        $pathway = $app->getPathWay();
        if ($menuitem) {
            $pathwayKeys = array_keys($pathway->getPathway());
            $lastIndex = end($pathwayKeys);
            if ($lastIndex !== false) {
                $pathway->setItemName($lastIndex, $menuitem->title);
            }
        }

        // Archive handling
        if ($task === 'archive') {
            $pathway->addItem(Text::_('com_planjeagenda_ARCHIVE'),
                Route::_('index.php?option=com_planjeagenda&view=eventslist&task=archive'));

            $print_link   = $uri->toString() . '?task=archive&print=1';
            $archive_link = Route::_('index.php?option=com_planjeagenda&view=eventslist');
            $pagetitle   .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
            $pageheading .= ' - ' . Text::_('com_planjeagenda_ARCHIVE');
        } else {
            $print_link   = $uri->toString() . '?tmpl=component&print=1';
            $archive_link = $uri->toString();
        }

        // Add site name to title
        $sitenameMode = $app->get('sitename_pagetitles', 0);
        if ($sitenameMode === 1) {
            $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
        } elseif ($sitenameMode === 2) {
            $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
        }

        $document->setTitle($pagetitle);
        $document->setMetaData('title', $pagetitle);

        // Permissions
        $permissions = new \stdClass();
        $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
        $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');

        // Assign variables to the template
        $this->lists         = $lists; // Gevuld met de actuele filters en selectielijst
        $this->rows          = $rows;
        $this->noevents      = $noevents;
        $this->print_link    = $print_link;
        $this->archive_link  = $archive_link;
        $this->params        = $params;
        $this->permissions   = $permissions;
        $this->pagination    = $this->get('Pagination');
        $this->action        = $uri->toString();
        $this->task          = $task;
        $this->jemsettings   = $jemsettings;
        $this->settings      = $settings;
        $this->pageclass_sfx = htmlspecialchars($pageclass_sfx);
        $this->pagetitle     = $pagetitle;

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document (metadata, feeds, etc.)
     */
    protected function _prepareDocument()
    {
        $document = Factory::getApplication()->getDocument();

        if ($this->params->get('menu-meta_description')) {
            $document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $document->setMetadata('robots', $this->params->get('robots'));
        }

        // Feed links
        if ($this->params->get('show_feed_link', 1)) {
            $link = '&format=feed&limitstart=';
            $title = htmlspecialchars($document->getTitle());

            $attribs = ['type' => 'application/rss+xml', 'title' => $title];
            $document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);

            $attribs = ['type' => 'application/atom+xml', 'title' => $title];
            $document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
        }
    }
}