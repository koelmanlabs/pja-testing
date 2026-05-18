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
use Joomla\CMS\Component\ComponentHelper;

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

        // initialize variables
        $app          = Factory::getApplication();
        $document     = $app->getDocument();
        $menu         = $app->getMenu();
        $menuitem     = $menu->getActive();
        $user         = Factory::getApplication()->getIdentity();
        
        
        // Haal parameters op
        $params   = $app->isClient('administrator') ? ComponentHelper::getParams('com_planjeagenda') : $app->getParams();
        $jemsettings  = PlanjeagendaHelper::config();
        $settings     = PlanjeagendaHelper::globalattribs();
        
        // asset loading
        PlanjeagendaHelper::loadCustomCss();
        PlanjeagendaHelper::loadCustomTag();

        
        // Probeer het model te laden voor rechtencontrole (categorieën)
        $model = $app->bootComponent('com_planjeagenda')
        ->getMVCFactory()
        ->createModel('Eventslist', 'Site', ['ignore_request' => false]);
        
        if (!$model) {
            throw new \RuntimeException('Eventslist model not found');
        }

        $top_category = (int)$params->get('top_category', 0);
        $jinput       = $app->input;
        $print        = $jinput->getBool('print', false);

        $this->param_topcat = $top_category > 0 ? ('&topcat='.$top_category) : '';
        $url             = Uri::root();

        if ($print) {
            PlanjeagendaHelper::loadCss('print');
            $document->setMetaData('robots', 'noindex, nofollow');
        }
        
        $model = $app->bootComponent('com_planjeagenda')
        ->getMVCFactory()
        ->createModel('Calendar', 'Site', ['ignore_request' => false]);
        
        
        if (!$model) {
            throw new \RuntimeException('Eventslist model not found');
        };
        
        // Paginatitel instellen conform Joomla standaarden
        $pagetitle = $params->def('page_title', $menuitem ? $menuitem->title : Text::_('COM_PLANJEAGENDA_CALENDAR'));
        $params->def('page_heading', $pagetitle);
        $pageclass_sfx = $params->get('pageclass_sfx', '');
        
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $pagetitle = Text::sprintf('JPAGETITLE', $app->get('sitename'), $pagetitle);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $pagetitle = Text::sprintf('JPAGETITLE', $pagetitle, $app->get('sitename'));
        }
        
        $document->setTitle($pagetitle);
        $document->setMetaData('title', $pagetitle);
        
        
        
        // Haal de database-driver op uit de container
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        
        // Bouw een snelle, efficiënte query om actieve categorieën op te halen
        $query->select($db->quoteName(['id', 'title', 'color']))
        ->from($db->quoteName('#__pja_categories'))
        ->where($db->quoteName('published') . ' = 1')
        ->order($db->quoteName('title') . ' ASC');
        
        $db->setQuery($query);
        
        try {
            $catIds = $db->loadObjectList();
        } catch (\Exception $e) {
            // Fallback naar een lege array als de tabel niet bestaat of leeg is
            $catIds = [];
        }
        
        // Rechten controleren voor de template
        $permissions = new \stdClass();
        $permissions->canAddEvent = $user->authorise('core.create', 'com_planjeagenda');

        $this->params        = $params;
        $this->jemsettings   = $jemsettings;
        $this->settings      = $settings;
        $this->permissions   = $permissions;
        $this->pageclass_sfx = $pageclass_sfx ? htmlspecialchars($pageclass_sfx) : $pageclass_sfx;
        $this->print_link    = $print_link;
        $this->print         = $print;
        $this->ical_link    = $ical_link;
        $this->catIds = $catIds;
        $this->municipalities = $model->getMunicipalities();
 
        
        /*
         $itemid  = $jinput->getInt('Itemid', 0);
         
         $partItemid = ($itemid > 0) ? '&Itemid=' . $itemid : '';
         $partDate = ($year ? ('&yearID=' . $year) : '') . ($month ? ('&monthID=' . $month) : '');
         $url_base = 'index.php?option=com_planjeagenda&view=calendar';
         
         $print_link = Route::_($url_base . $partItemid. $partDate . '&print=1&tmpl=component');
         $ical_link = $partDate;
         */
        
        /* -- disabled -- PlanjeagendaHelper::loadCss('klevents'); */
        /* -- disabled -- PlanjeagendaHelper::loadCss('calendar'); */
 
 /*
        $evlinkcolor = $params->get('eventlinkcolor');
        $evbackgroundcolor = $params->get('eventbackgroundcolor');
        $currentdaycolor = $params->get('currentdaycolor');
        $eventandmorecolor = $params->get('eventandmorecolor');
       */
        
        /*
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
         */
        
        
/*        $year  = (int)$jinput->getInt('yearID', date("Y")); */
 /*       $month = (int)$jinput->getInt('monthID', date("m")); */
           
        /*  -- disabled --     $document->addStyleDeclaration($style); */
        /*  -- disabled --     $document->addScript($url.'media/com_planjeagenda/js/calendar.js'); */
        
        // get data from model and set the month
        // $model = $this->getModel(); // eventslistmodel
        
        /// $model->setState('calendar.year', $year);
        /// $model->setState('calendar.month', $month);
        
        /*  -- disabled --      $model->setState('calendar.year', $year); */
        /*  -- disabled --      $model->setState('calendar.month', $month); */
        /* -- disabled -- $model->setDate(mktime(0, 0, 1, $month, 1, $year)); */
        

        parent::display($tpl);
    }
}
