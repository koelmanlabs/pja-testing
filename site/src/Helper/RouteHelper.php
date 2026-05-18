<?php

namespace KoelmanLabs\Component\Planjeagenda\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Language\Multilanguage;

/**
 * Route Helper for com_planjeagenda
 * 
 * Rewritten to follow native Joomla core component architecture patterns.
 */
class RouteHelper
{
    /**
     * Get the SEF URL route for a single Event detail page
     *
     * @param   string   $id        The route slug of the content item (e.g., '76:alias' or '76').
     * @param   integer  $catid     The category ID.
     * @param   string   $language  The language code.
     * @param   string   $layout    The layout value.
     *
     * @return  string  The internal routing URL
     */
    public static function getEventRoute($id, $catid = 0, $language = null, $layout = null)
    {
        // Build the basic link structure with the item ID/slug parameter
        $link = 'index.php?option=com_planjeagenda&view=event&id=' . $id;

        if ((int) $catid > 1) {
            $link .= '&catid=' . $catid;
        }

        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Get the SEF URL route for an Events list or a Category layout
     *
     * @param   mixed    $catid     The category ID or CategoryNode object.
     * @param   string   $language  The language code.
     * @param   string   $layout    The layout value.
     *
     * @return  string  The internal routing URL
     */
    public static function getEventslistRoute($catid, $language = null, $layout = null)
    {
        if ($catid instanceof CategoryNode) {
            $id = $catid->id;
        } else {
            $id = (int) $catid;
        }

        if ($id < 1) {
            return '';
        }

        $link = 'index.php?option=com_planjeagenda&view=eventslist&id=' . $id;

        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Get the SEF URL route for a single Venue profile page
     *
     * @param   string   $id        The route slug of the venue item (e.g., '8:venue-alias').
     * @param   string   $language  The language code.
     * @param   string   $layout    The layout value.
     *
     * @return  string  The internal routing URL
     */
    public static function getVenueRoute($id, $language = null, $layout = null)
    {
        $link = 'index.php?option=com_planjeagenda&view=venue&id=' . $id;

        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }
    
    
    
    
    
    public static function getCategoryRoute($catid, $language = null, $layout = null)
    {
        if ($catid instanceof CategoryNode) {
            $id = $catid->id;
        } else {
            $id = (int) $catid;
        }
        
        if ($id < 1) {
            return '';
        }
        
        $link = 'index.php?option=com_planjeagenda&view=category&id=' . $id;
        
        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }
        
        if ($layout) {
            $link .= '&layout=' . $layout;
        }
        
        return $link;
    }
    
    
    
    
    
    
    
    
    
    
    

    /**
     * Get the front-end event edit form route.
     *
     * @param   integer  $id  The event record item ID.
     *
     * @return  string  The edit form route.
     */
    public static function getFormRoute($id)
    {
        return 'index.php?option=com_planjeagenda&task=event.edit&a_id=' . (int) $id;
    }
}