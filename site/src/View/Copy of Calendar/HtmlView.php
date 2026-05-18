<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Calendar;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\Calendar;

/**
 * Calendar-View
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Creates the Calendar View
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
        $menu         = $app->getMenu();
        $menuitem     = $menu->getActive();
        $jemsettings  = PlanjeagendaHelper::config();
        $settings     = PlanjeagendaHelper::globalattribs();
        $user         = Factory::getApplication()->getIdentity();
        $params       = ($app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams());
        $top_category = (int)$params->get('top_category', 0);
        $jinput       = $app->input;
        $print        = $jinput->getBool('print', false);

        $this->param_topcat = $top_category > 0 ? ('&topcat='.$top_category) : '';
        $url             = Uri::root();

        // Load css
        PlanjeagendaHelper::loadCss('klevents');
        PlanjeagendaHelper::loadCss('calendar');
        PlanjeagendaHelper::loadCustomCss();
        PlanjeagendaHelper::loadCustomTag();

        if ($print) {
            PlanjeagendaHelper::loadCss('print');
            $document->setMetaData('robots', 'noindex, nofollow');
        }

        $evlinkcolor = $params->get('eventlinkcolor');
        $evbackgroundcolor = $params->get('eventbackgroundcolor');
        $currentdaycolor = $params->get('currentdaycolor');
        $eventandmorecolor = $params->get('eventandmorecolor');

        $style = '
        div#klevents .eventcontentinner a,
        div#klevents .eventandmore a {
            color:' . $evlinkcolor . ';
        }
        .eventcontentinner {
            background-color:'.$evbackgroundcolor .';
        }
        .eventandmore {
            background-color:'.$eventandmorecolor .';
        }

        .today .daynum {
            background-color:'.$currentdaycolor.';
        }';

        $document->addStyleDeclaration($style);
        $document->addScript($url.'media/com_planjeagenda/js/calendar.js');

        $year  = (int)$jinput->getInt('yearID', date("Y"));
        $month = (int)$jinput->getInt('monthID', date("m"));

        // get data from model and set the month
        // $model = $this->getModel(); // eventslistmodel
        
        /// $model->setState('calendar.year', $year);
        /// $model->setState('calendar.month', $month);
        
        $model = $app->bootComponent('com_planjeagenda')
        ->getMVCFactory()
        ->createModel('Eventslist', 'Site', ['ignore_request' => false]);
        
        
        if (!$model) {
            throw new \RuntimeException('Eventslist model not found');
        };
        
        $model->setState('calendar.year', $year);
        $model->setState('calendar.month', $month);
        
       // $model->setDate(mktime(0, 0, 1, $month, 1, $year));
        

        $rows = $model->getItems();

        // Set Page title
        $pagetitle = $params->def('page_title', $menuitem->title);
        $params->def('page_heading', $pagetitle);
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
        $catIds = $model->getCategories('all');
        $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');
        $permissions->canAddVenue = $user->authorise('core.create', 'com_planjeagenda');

        $itemid  = $jinput->getInt('Itemid', 0);

        $partItemid = ($itemid > 0) ? '&Itemid=' . $itemid : '';
        $partDate = ($year ? ('&yearID=' . $year) : '') . ($month ? ('&monthID=' . $month) : '');
        $url_base = 'index.php?option=com_planjeagenda&view=calendar';

        $print_link = Route::_($url_base . $partItemid. $partDate . '&print=1&tmpl=component');
        $ical_link = $partDate;
        

        $this->rows          = $rows;
        $this->catIds        = $catIds;
        $this->params        = $params;
        $this->jemsettings   = $jemsettings;
        $this->settings      = $settings;
        $this->permissions   = $permissions;
        $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;
        $this->print_link    = $print_link;
        $this->print         = $print;
        $this->ical_link    = $ical_link;

        parent::display($tpl);
    }
}
