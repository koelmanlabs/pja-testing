<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

require_once(JPATH_SITE.'/components/com_planjeagenda/classes/categories.class.php');

/**
 * JEM Component Route Helper
 * based on Joomla ContentHelperRoute
 *
 * @static
 * @package        JEM
 *
 */
abstract class RouteHelper
{
    protected static $lookup;
    const ARTIFICALID = 0;

    /**
     * Determines an JEM Link
     *
     * @param int The id of an JEM item
     * @param string The view
     * @param string The category of the item
     * @deprecated Use specific Route methods instead!
     *
     * @return string determined Link
     */
    public static function getRoute($id, $view = 'event', $category = null)
    {

        // Deprecation warning.
        Log::add('PlanjeagendaHelperRoute::getRoute() is deprecated, use specific route methods instead.', Log::WARNING, 'deprecated');

        $needles = array(
            $view => array((int) $id)
        );

        if ($item = self::_findItem($needles)) {
            $link = 'index.php?Itemid='.$item;
        }
        else {
            // Create the link
            $link = 'index.php?option=com_planjeagenda&view='.$view.'&id='. $id;

            // Add category, if available
            if(!is_null($category)) {
                $link .= '&catid='.$category;
            }

            if ($item = self::_findItem($needles)) {
                $link .= '&Itemid='.$item;
            }
            elseif ($item = self::_findItem()) {
                $link .= '&Itemid='.$item;
            }
        }

        return $link;
    }

    public static function getCategoryRoute($id, $task = '')
    {
        $settings      = PlanjeagendaHelper::globalattribs();
        $defaultItemid = $settings->get('default_Itemid');

        $needles = array(
            'category'   => array((int) $id),
            'categories' => array(self::ARTIFICALID),
        );

        $category = new PlanjeagendaCategories($id);
        if ($category) {
            $needles['categories'] = array_reverse($category->getPath());
        }

        // Always include the id in the link so it is never lost
        $link = 'index.php?option=com_planjeagenda&view=category&id=' . (int) $id;

        if (!empty($task)) {
            $link .= '&task=' . $task;
        }

        // Only use a specific menu item if it matches THIS category id
        if ($item = self::_findItem(array('category' => array((int) $id)))) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::_findItem(array('categories' => $needles['categories']))) {
            $link .= '&Itemid=' . $item;
        } elseif (!empty($defaultItemid)) {
            $link .= '&Itemid=' . $defaultItemid;
        }

        return $link;
    }

    public static function getEventRoute($id, $catid = null)
    {
        $settings         = PlanjeagendaHelper::globalattribs();
        $defaultItemid     = $settings->get('default_Itemid');

        $needles = array(
            'event' => array((int) $id)
        );

        // Create the link
        $link = 'index.php?option=com_planjeagenda&view=event&id='. $id;

        // Add category, if available
        if(!is_null($catid)) {
            // TODO
            //$needles['categories'] = $needles['category'];
            $link .= '&catid='.$catid;
        }

        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item;
        }
        elseif ($item = self::_findItem()) {
            // $link .= '&Itemid='.$item;
            if (isset($defaultItemid))
            {
                $link .= '&Itemid='.$defaultItemid;
            }
        }

        return $link;
    }

    public static function getVenueRoute($id)
    {
        $settings         = PlanjeagendaHelper::globalattribs();
        $defaultItemid     = $settings->get('default_Itemid');

        $needles = array(
            'venue' => array((int) $id)
        );

        // Create the link
        $link = 'index.php?option=com_planjeagenda&view=venue&id='. $id;

        // If no venue view works try venues
        $needles['venues'] = array(self::ARTIFICALID);

        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item;
        }
        elseif ($item = self::_findItem()) {
            if (isset($defaultItemid))
            {
                $link .= '&Itemid='.$defaultItemid;
            }
        }

        return $link;
    }

    protected static function getRouteWithoutId($my)
    {
        $settings         = PlanjeagendaHelper::globalattribs();
        $defaultItemid     = $settings->get('default_Itemid');

        $needles = array();
        $needles[$my] = array(self::ARTIFICALID);

        // Create the link
        $link = 'index.php?option=com_planjeagenda&view='.$my;

        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item;
        }
        elseif ($item = self::_findItem()) {
            if (isset($defaultItemid))
            {
                $link .= '&Itemid='.$defaultItemid;
            } else {
                $link .= '&Itemid='.$item;
            }
        }

        return $link;
    }

    public static function getMyAttendancesRoute()
    {
        return self::getRouteWithoutId('myattendances');
    }

    public static function getMyEventsRoute()
    {
        return self::getRouteWithoutId('myevents');
    }

    public static function getMyVenuesRoute()
    {
        return self::getRouteWithoutId('myvenues');
    }


    /**
     * Determines the Itemid
     *
     * searches if a menuitem for this item exists
     * if not the active menuitem will be returned
     *
     * @param array The id and view
     *
     *
     * @return int Itemid
     */
    protected static function _findItem($needles = null)
    {
        $app = Factory::getApplication();
        $menus = $app->getMenu('site');

        // Prepare the reverse lookup array.
        if (self::$lookup === null) {
            self::$lookup = array();

            $component = ComponentHelper::getComponent('com_planjeagenda');
            $items = $menus->getItems('component_id', $component->id);

            if ($items) {
                foreach ($items as $item)
                {
                    if (isset($item->query) && isset($item->query['view'])) {
                        if (isset($item->query['layout']) && ($item->query['layout'] == 'calendar')) {
                            continue; // skip calendars
                        }

                        $view = $item->query['view'];

                        if (!isset(self::$lookup[$view])) {
                            self::$lookup[$view] = array();
                        }

                        if (isset($item->query['id'])) {
                            self::$lookup[$view][$item->query['id']] = $item->id;
                        }
                        // Some views have no ID, but we have to set one
                        else {
                            self::$lookup[$view][self::ARTIFICALID] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view => $ids)
            {
                if (isset(self::$lookup[$view])) {
                    foreach($ids as $id)
                    {
                        if (isset(self::$lookup[$view][(int)$id])) {
                            // TODO: Check on access. See commented code below
                            return self::$lookup[$view][(int)$id];
                        }
                    }
                }
            }
        }
        else {
            $active = $menus->getActive();
            if ($active) {
                return $active->id;
            }
        }

        return null;

//         $user = \Joomla\CMS\Factory::getApplication()->getIdentity();

//         //false if there exists no menu item at all
//         if (!$items) {
//             return false;
//         } else {
//             //Not needed currently but kept because of a possible hierarchic link structure in future
//             foreach($needles as $needle => $id)
//             {
//                 foreach($items as $item)
//                 {
//                     if (($item->query['view'] == $needle) && ($item->query['id'] == $id)) {
//                         return $item;
//                     }
//                 }

//                 /*
//                 //no menuitem exists -> return first possible match
//                 foreach($items as $item)
//                 {
//                     if ($item->published == 1 && $item->access <= $gid) {
//                         return $item;
//                     }
//                 }
//                 */
//             }
//         }

//         return false;
    }
}
