<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Application\ApplicationHelper;
// use KoelmanLabs\Component\Planjeagenda\Site\Helper\IcalHelper;


class PlanjeagendaHelper 
{
    
    /**
     * Pulls settings from database and stores in an static object
     *
     * @return object
     */
    static public function config()
    {
        static $config;
        
        if (!is_object($config)) {
            $jemConfig = PlanjeagendaConfig::getInstance();
            $config = clone $jemConfig->toObject(); // We need a copy to ensure not to store 'params' we add below!
            
            $config->params = ComponentHelper::getParams('com_planjeagenda');
        }
        
        return $config;
    }
    
    /**
     * Pulls settings from database and stores in an static object
     *
     * @return object
     */
    static public function globalattribs()
    {
        static $globalregistry;
        if (!is_object($globalregistry)) {
            $config = self::config();
            if (!$config) {
                return new Registry();
            }
            $attribs = $config->globalattribs ?? null;
            // globalattribs may already be a Registry object from loadData()
            if ($attribs instanceof Registry) {
                $globalregistry = $attribs;
            } else {
                $globalregistry = new Registry($attribs);
            }
        }
        
        return $globalregistry;
    }
    
    
    /*
     * Search for a CSS file
     */
    static public function loadCss($css)
    {
        $settings = self::retrieveCss();
        $suffix   = self::getLayoutStyleSuffix();
        $app      = Factory::getApplication();
        $document = $app->getDocument();
        $uri      = Uri::getInstance();
        $url      = $uri->root();
        if (!empty($suffix)) {
            $suffix = '-' . $suffix;
        }
        
        if ($settings->get('css_' . $css . '_usecustom', '0')) {
            
            # we want to use custom so now check if we've a file
            $file = $settings->get('css_' . $css . '_customfile');
            $is_file = false;
            
            # something was filled, now check if we've a valid file
            if ($file) {
                $file = preg_replace('%^/([^/]*)%', '$1', $file); // remove leading single slash
                $is_file = is_file(JPATH_SITE . '/media/com_klevents/css/custom/' . $file);
                
                if ($is_file) {
                    # at this point we do have a valid file but let's check the extension too.
                    $ext =  File::getExt($file);
                    if ($ext != 'css') {
                        # the file is valid but the extension not so let's return false
                        $is_file = false;
                    }
                }
            }
            
            if ($is_file) {
                # we do have a valid file so we will use it.
                // $css = HTMLHelper::_('stylesheet', $file, array(), false);
                $css = $document->addStyleSheet($url.'media/com_klevents/css/custom/' . $file);
            } else {
                # unfortunately we don't have a valid file so we're looking at the default
                // $files = HTMLHelper::_('stylesheet', 'com_klevents/' . $css . $suffix . '.css', array(), true, true);
                $files = $document->addStyleSheet($url.'media/com_klevents/css/custom/' . $css . $suffix . '.css');
                if (!empty($files)) {
                    # we have to call this stupid function twice; no other way to know if something was loaded
                    // $css = HTMLHelper::_('stylesheet', 'com_klevents/' . $css . $suffix . '.css', array(), true);
                    $css = $document->addStyleSheet($url.'media/com_klevents/css/custom/' . $css . $suffix . '.css');
                    
                } else {
                    # no css for layout style configured, so use the default css
                    // $css = HTMLHelper::_('stylesheet', 'com_klevents/' . $css . '.css', array(), true);
                    $css = $document->addStyleSheet($url.'media/com_klevents/css/custom/'. $css. '.css');
                    
                }
            }
        } else {
            # here we want to use the normal css
            // $files = HTMLHelper::_('stylesheet', 'com_klevents/' . $css . $suffix . '.css', array(), true, true);
            $files = $document->addStyleSheet($url.'media/com_klevents/css/' . $css . $suffix . '.css');
            
            if (!empty($files)) {
                # we have to call this stupid function twice; no other way to know if something was loaded
                // $css = HTMLHelper::_('stylesheet', 'com_klevents/' . $css . $suffix . '.css', array(), true);
                $css = $document->addStyleSheet($url.'media/com_klevents/css/' . $css . $suffix . '.css');
                
            } else {
                # no css for layout style configured, so use the default css
                // $css = HTMLHelper::_('stylesheet', 'com_klevents/' . $css . '.css', array(), true);
                $css = $document->addStyleSheet($url.'media/com_klevents/css/'. $css. '.css');
                
            }
        }
        
        return $css;
    }
    
    
    
    static public function sanitizeOrderCol($col, array $allowed, $default = 'a.dates')
    {
        return in_array($col, $allowed, true) ? $col : $default;
    }
    
    /**
     * Adds attendees numbers to rows
     *
     * @param  $data reference to event rows
     * @return false on error, $data on success
     */
    static public function getAttendeesNumbers(& $data)
    {
        // Make sure this is an array and it is not empty
        if (!is_array($data) || !count($data)) {
            return false;
        }
        
        // Get the ids of events
        $ids = array();
        foreach ($data as $event) {
            $ids[] = (int)$event->id;
        }
        $ids = implode(",", $ids);
        
        $db = Factory::getContainer()->get('DatabaseDriver');
        
        // status 1: user registered (attendee or waiting list), status -1: user exlicitely unregistered, status 0: user is invited but hadn't answered yet
        $query = ' SELECT COUNT(id) as total,'
            . '        SUM(IF(status =  1 AND waiting = 0, places, 0)) AS registered,'
                . '        SUM(IF(status =  1 AND waiting >  0, places, 0)) AS waiting,'
                    . '        SUM(IF(status = -1,                  places, 0)) AS unregistered,'
                        . '        SUM(IF(status =  0,                  places, 0)) AS invited,'
                            . '        event '
                                . ' FROM #__pja_register '
                                    . ' WHERE event IN (' . $ids .')'
                                        . ' GROUP BY event ';
                                        
                                        $db->setQuery($query);
                                        $res = $db->loadObjectList('event');
                                        
                                        foreach ($data as $k => &$event) { // by reference for direct edit
                                            if (isset($res[$event->id])) {
                                                $event->regTotal   = $res[$event->id]->total;
                                                $event->regCount   = $res[$event->id]->registered;
                                                $event->reserved   = $event->reservedplaces;
                                                $event->waiting    = $res[$event->id]->waiting;
                                                $event->unregCount = $res[$event->id]->unregistered;
                                                $event->invited    = $res[$event->id]->invited;
                                            } else {
                                                $event->regTotal   = 0;
                                                $event->regCount   = 0;
                                                $event->reserved   = 0;
                                                $event->waiting    = 0;
                                                $event->unregCount = 0;
                                                $event->invited    = 0;
                                            }
                                            $event->available = max(0, $event->maxplaces - $event->regCount -$event->reservedplaces);
                                        }
                                        
                                        return $data;
    }
    
    
    
    
    /**
     * return initialized calendar tool class for ics export
     *
     * @return object
     */
    static public function getCalendarTool()
    {
        $timezone_name = self::getTimeZoneName();
        
        $vcal = new IcalHelper();
        $vcal->setProperty("calscale", "GREGORIAN");
        $vcal->setProperty('method', 'PUBLISH');
        if ($timezone_name) {
            $vcal->setProperty("X-WR-TIMEZONE", $timezone_name);
        }
        return $vcal;
    }
    
    static public function icalAddEvent(&$calendartool, $event)
    {
        // debug log
        $jemsettings   = self::config();
        $timezone_name = self::getTimeZoneName();
        $config        = Factory::getConfig();
        $sitename      = $config->get('sitename');
        $uri           = Uri::getInstance();
        
        // get categories names
        $categories = array();
        foreach ($event->categories as $c) {
            $categories[] = $c->catname;
        }
        
        // no start date...
        $validdate = self::isValidDate($event->dates);
        
        if (!$event->dates || !$validdate) {
            return false;
        }
        
        // make end date same as start date if not set
        if (!$event->enddates) {
            $event->enddates = $event->dates;
        }
        
        // start
        if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $event->dates, $start_date)) {
            throw new \Exception(Text::_('COM_KLEVENTS_ICAL_EXPORT_WRONG_STARTDATE_FORMAT'), 0);
        }
        
        $date = array('year' => (int) $start_date[1], 'month' => (int) $start_date[2], 'day' => (int) $start_date[3]);
        
        // all day event if start time is not set
        if (!$event->times) // all day !
        {
            $dateparam = array('VALUE' => 'DATE');
            
            // for ical all day events, dtend must be send to the next day
            $event->enddates = date('Y-m-d', strtotime($event->enddates.' +1 day'));
            
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $event->enddates, $end_date)) {
                throw new \Exception(Text::_('COM_KLEVENTS_ICAL_EXPORT_WRONG_ENDDATE_FORMAT'), 0);
            }
            
            $date_end = array('year' => $end_date[1], 'month' => $end_date[2], 'day' => $end_date[3]);
            $dateendparam = array('VALUE' => 'DATE');
        }
        else // not all day events, there is a start time
        {
            if (!preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})/', $event->times, $start_time)) {
                throw new \Exception(Text::_('COM_KLEVENTS_ICAL_EXPORT_WRONG_STARTTIME_FORMAT'), 0);
            }
            
            $date['hour'] = $start_time[1];
            $date['min']  = $start_time[2];
            $date['sec']  = $start_time[3];
            $dateparam = array('VALUE' => 'DATE-TIME');
            if ($jemsettings->ical_tz == 1) {
                $dateparam['TZID'] = $timezone_name;
            }
            
            if (!$event->endtimes || $event->endtimes == '00:00:00') {
                $event->endtimes = $event->times;
            }
            
            // if same day but end time < start time, change end date to +1 day
            if ($event->enddates == $event->dates &&
                strtotime($event->dates.' '.$event->endtimes) < strtotime($event->dates.' '.$event->times))
            {
                $event->enddates = date('Y-m-d', strtotime($event->enddates.' +1 day'));
            }
            
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $event->enddates, $end_date)) {
                throw new \Exception(Text::_('COM_KLEVENTS_ICAL_EXPORT_WRONG_ENDDATE_FORMAT'), 0);
            }
            
            $date_end = array('year' => $end_date[1], 'month' => $end_date[2], 'day' => $end_date[3]);
            
            if (!preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})/', $event->endtimes, $end_time)) {
                throw new \Exception(Text::_('COM_KLEVENTS_ICAL_EXPORT_WRONG_STARTTIME_FORMAT'), 0);
            }
            
            $date_end['hour'] = $end_time[1];
            $date_end['min']  = $end_time[2];
            $date_end['sec']  = $end_time[3];
            $dateendparam = array('VALUE' => 'DATE-TIME');
            if ($jemsettings->ical_tz == 1) {
                $dateendparam['TZID'] = $timezone_name;
            }
        }
        
        // item description text
        $description = $event->title.'\\n';
        $description .= Text::_('COM_KLEVENTS_CATEGORY').': '.implode(', ', $categories).'\\n';
        
        $link = $uri->root().RouteHelper::getEventRoute($event->slug);
        $link = Route::_($link);
        $description .= Text::_('COM_KLEVENTS_ICS_LINK').': '.$link.'\\n';
        
        // location
        $location = array($event->venue);
        if (isset($event->street) && !empty($event->street)) {
            $location[] = $event->street;
        }
        
        if (isset($event->postalCode) && !empty($event->postalCode) && isset($event->city) && !empty($event->city)) {
            $location[] = $event->postalCode.' '.$event->city;
        } else {
            if (isset($event->postalCode) && !empty($event->postalCode)) {
                $location[] = $event->postalCode;
            }
            if (isset($event->city) && !empty($event->city)) {
                $location[] = $event->city;
            }
        }
        
        if (isset($event->countryname) && !empty($event->countryname)) {
            $exp = explode(",",$event->countryname);
            $location[] = $exp[0];
        }
        
        $location = implode(",", $location);
        
        $calendartool->addEvent([
            'summary'     => ['value' => $event->title],
            'categories'  => ['value' => implode(', ', $categories)],
            'dtstart'     => ['value' => $date,     'params' => $dateparam],
            'dtend'       => count($date_end) ? ['value' => $date_end, 'params' => $dateendparam] : null,
            'description' => ['value' => $description],
            'location'    => $location !== '' ? ['value' => $location] : null,
            'url'         => ['value' => $link],
            'uid'         => ['value' => 'event'.$event->id.'@'.$sitename],
        ]);
        return true;
    }
    
    
    
    /**
     * returns timezone name
     */
    static public function getTimeZoneName()
    {
        $user     = Factory::getApplication()->getIdentity();
        $userTz   = $user->getParam('timezone');
        $timeZone = Factory::getConfig()->get('offset');
        
        /* disabled for now
         if($userTz) {
         $timeZone = $userTz;
         }
         */
        return $timeZone;
    }
    
    
    /**
     * Used in: RawView (ics)
     * 
     * return true is a date is valid (not null, or 0000-00...)
     *
     * @param  string $date
     * @return boolean
     */
    static public function isValidDate($date)
    {
        if (is_null($date)) {
            return false;
        }
        if ($date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
            return false;
        }
        if (!strtotime($date)) {
            return false;
        }
        return true;
    }
    
    
    /**
     * called from viewphp
     * Retrieves the CSS-settings from database and stores in an static object
     */
    static public function retrieveCss()
    {
        static $registryCSS;
        if (!is_object($registryCSS)) {
            $registryCSS = new Registry(self::config()->css);
        }
        
        return $registryCSS;
    }
    
    
    /*
     * called from viewphp
     */
    static public function getLayoutStyleSuffix()
    {
        $jemsettings = self::config();
        $layoutstyle = isset($jemsettings->layoutstyle) ? (int)$jemsettings->layoutstyle : 0;
        
        return $layoutstyle === 1 ? 'responsive' : '';
        
    }
    
    /**
     * called from viewphp
     * Load Custom CSS
     *
     * @return boolean
     */
    static public function loadCustomCss()
    {
        $app         = Factory::getApplication();
        $document    = $app->getDocument();
        $settings    = self::retrieveCss();
        $jemsettings = self::config();
        $layoutstyle = isset($jemsettings->layoutstyle) ? (int)$jemsettings->layoutstyle : 0;
        $style       = "";
        
        # background-colors
        $bg_filter            = $settings->get('css_color_bg_filter');
        $bg_h2                = $settings->get('css_color_bg_h2');
        $bg_jem               = $settings->get('css_color_bg_jem');
        $bg_table_th          = $settings->get('css_color_bg_table_th');
        $bg_table_td          = $settings->get('css_color_bg_table_td');
        $bg_table_tr_entry2   = $settings->get('css_color_bg_table_tr_entry2');
        $bg_table_tr_hover    = $settings->get('css_color_bg_table_tr_hover');
        $bg_table_tr_featured = $settings->get('css_color_bg_table_tr_featured');
        # border-colors
        $border_filter        = $settings->get('css_color_border_filter');
        $border_h2            = $settings->get('css_color_border_h2');
        $border_table_th      = $settings->get('css_color_border_table_th');
        $border_table_td      = $settings->get('css_color_border_table_td');
        # font-color
        $font_table_h2        = $settings->get('css_color_font_h2');
        $font_table_th        = $settings->get('css_color_font_table_th');
        $font_table_td        = $settings->get('css_color_font_table_td');
        $font_table_td_a      = $settings->get('css_color_font_table_td_a');
        
        switch ($layoutstyle) {
            case 1: // 'Default (Responsive Style)'
                if (!empty($bg_filter)) {
                    $style .= "div#klevents #jem_filter {background-color:".$bg_filter.";}";
                }
                if (!empty($bg_h2)) {
                    $style .= "div#klevents h2 {background-color:".$bg_h2.";}";
                }
                if (!empty($bg_jem)) {
                    $style .= "div#klevents {background-color:".$bg_jem.";}";
                }
                if (!empty($bg_table_th)) {
                    $style .= "div#klevents .klevents-misc, div#klevents .klevents-sort-small {background-color:" . $bg_table_th . ";}";
                }
                if (!empty($bg_table_td)) { //Caused by the row-layout of JEM-Responsive, there exist no cells, we use that for row-color
                    $style .= "div#klevents .eventlist li:nth-child(odd) {background-color:" . $bg_table_td . ";}";
                }
                if (!empty($bg_table_tr_entry2)) {
                    $style .= "div#klevents .eventlist li:nth-child(even) {background-color:" . $bg_table_tr_entry2 . ";}";
                }
                if (!empty($bg_table_tr_featured)) {
                    $style .= "div#klevents .eventlist .klevents-featured {background-color:" . $bg_table_tr_featured . ";}";
                }
                // Important: :hover must be after .featured to overrule
                if (!empty($bg_table_tr_hover)) {
                    $style .= "div#klevents .eventlist li:hover {background-color:" . $bg_table_tr_hover . ";}";
                }
                if (!empty($border_filter)) {
                    $style .= "div#klevents #jem_filter {border: 1px solid " . $border_filter . ";}";
                }
                if (!empty($border_h2)) {
                    $style .= "div#klevents h2 {border: 1px solid " . $border_h2 . ";}";
                }
                if (!empty($border_table_th)) {
                    $style .= "div#klevents .klevents-misc, div#klevents .klevents-sort-small {border: 1px solid " . $border_table_th . ";}";
                }
                if (!empty($border_table_td)) {
                    $style .= "div#klevents .klevents-event, div#klevents .klevents-event:first-child {border-color: " . $border_table_td . ";}";
                }
                if (!empty($font_table_h2)) {
                    $style .= "div#klevents h2 {color:" . $font_table_h2 . ";}";
                }
                if (!empty($font_table_th)) {
                    $style .= "div#klevents .klevents-misc, div#klevents .klevents-sort-small {color:" . $font_table_th . ";}";
                }
                if (!empty($font_table_td)) {
                    $style .= "div#klevents .klevents-event {color:" . $font_table_td . ";}";
                }
                if (!empty($font_table_td_a)) {
                    $style .= "div#klevents .klevents-event a {color:" . $font_table_td_a . ";}";
                }
                break;
            default: // 'Legacy (Table Style)'
                if (!empty($bg_filter)) {
                    $style .= "div#klevents #jem_filter {background-color:".$bg_filter.";}";
                }
                if (!empty($bg_h2)) {
                    $style .= "div#klevents h2 {background-color:".$bg_h2.";}";
                }
                if (!empty($bg_jem)) {
                    $style .= "div#klevents {background-color:".$bg_jem.";}";
                }
                if (!empty($bg_table_th)) {
                    $style .= "div#klevents table.eventtable th {background-color:" . $bg_table_th . ";}";
                }
                if (!empty($bg_table_td)) {
                    $style .= "div#klevents table.eventtable td {background-color:" . $bg_table_td . ";}";
                }
                if (!empty($bg_table_tr_entry2)) {
                    $style .= "div#klevents table.eventtable tr.sectiontableentry2 td {background-color:" . $bg_table_tr_entry2 . ";}";
                }
                if (!empty($bg_table_tr_featured)) {
                    $style .= "div#klevents table.eventtable tr.featured td {background-color:" . $bg_table_tr_featured . ";}";
                }
                // Important: :hover must be after .featured to overrule
                if (!empty($bg_table_tr_hover)) {
                    $style .= "div#klevents table.eventtable tr:hover td {background-color:" . $bg_table_tr_hover . ";}";
                }
                if (!empty($border_filter)) {
                    $style .= "div#klevents #jem_filter {border-color:" . $border_filter . ";}";
                }
                if (!empty($border_h2)) {
                    $style .= "div#klevents h2 {border-color:".$border_h2.";}";
                }
                if (!empty($border_table_th)) {
                    $style .= "div#klevents table.eventtable th {border-color:" . $border_table_th . ";}";
                }
                if (!empty($border_table_td)) {
                    $style .= "div#klevents table.eventtable td {border-color:" . $border_table_td . ";}";
                }
                if (!empty($font_table_h2)) {
                    $style .= "div#klevents h2 {color:" . $font_table_h2 . ";}";
                }
                if (!empty($font_table_th)) {
                    $style .= "div#klevents table.eventtable th {color:" . $font_table_th . ";}";
                }
                if (!empty($font_table_td)) {
                    $style .= "div#klevents table.eventtable td {color:" . $font_table_td . ";}";
                }
                if (!empty($font_table_td_a)) {
                    $style .= "div#klevents table.eventtable td a {color:" . $font_table_td_a . ";}";
                }
                break;
        } // switch
        
        $document->addStyleDeclaration($style);
        
        return true;
    }
    
    
    
    /**
     * called from viewphp
     * Loads Custom Tags
     *
     * @return boolean
     */
    static public function loadCustomTag()
    {
        // emtpy method
    }
    
    
    /**
     * return true is a time is valid (not null, or 00:00:00...)
     *
     * @param  string $time
     * @return boolean
     */
    static public function isValidTime($time)
    {
        if (is_null($time)) {
            return false;
        }
        
        if (!strtotime($time)) {
            return false;
        }
        return true;
    }
    
    
    
    /**
     * view: calendar
     * Creates a tooltip
     */
    static public function caltooltip($tooltip, $title = '', $text = '', $href = '', $class = '', $time = '', $color = '')
    {
        HTMLHelper::_('bootstrap.tooltip');
        if (0) { /* old style using 'hasTip' */
            $title = HTMLHelper::tooltipText($title, '<div style="font-weight:normal;">'.$tooltip.'</div>', 0);
        } else { /* new style using 'has Tooltip' */
            $class = str_replace('hasTip', '', $class) . ' hasTooltip';
            $title = HTMLHelper::tooltipText($title, $tooltip, 0); // this calls htmlspecialchars()
        }
        $tooltip = '';
        
        
        if ($href) {
            $href = Route::_ ($href);
            $tip = '<span class="'.$class.'" data-bs-toggle="tooltip" data-bs-html="true" data-bs-original-title="'.$title.$tooltip.'"><a href="'.$href.'">'.$time.$text.'</a></span>';
        } else {
            $tip = '<span class="'.$class.'" data-bs-toggle="tooltip" data-bs-html="true" data-bs-original-title="'.$title.$tooltip.'">'.$text.'</span>';
        }
        
        return $tip;
    }
    
    
    
    
}
