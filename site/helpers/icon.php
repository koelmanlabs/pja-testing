<?php

namespace KoelmanLabs\Component\Planjeagenda\Site\helpers;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;

// Voorkom directe toegang
defined('_JEXEC') or die;

class Icon
{
    /**
     * Creates html code to show an icon, using image or icon font depending on configuration.
     *
     * @param string      $image      Relative path to image (e.g. 'com_planjeagenda/archive_front.webp')
     * @param string      $icon       CSS classes for icon font (FontAwesome etc.)
     * @param string      $alt        Language string for alt/title
     * @param array|string $attribs   Extra attributes
     * @param boolean     $no_iconfont Force image (true = always image, false = respect config)
     * @param boolean     $relative   Relative to /media folder
     * @return string
     */
    public static function construct(
        $image, 
        $icon, 
        $alt, 
        $attribs = null, 
        $no_iconfont = false, 
        $relative = true
    )
    
    
    {
        $app = Factory::getApplication();
        
        // Respect component configuratie
        $config       = PlanjeagendaHelper::config();   // jouw helper
        $useIconfont  = !$no_iconfont && !empty($config->useiconfont);

        if (!$useIconfont) {
            // Gebruik Joomla's eigen image helper (beste praktijk in J6)
            $html = HTMLHelper::_('image', 
                $image, 
                Text::_($alt), 
                $attribs, 
                $relative
            );
        } 
        elseif (!empty($attribs)) {
            // Icon font met extra attributen
            $attr = is_array($attribs) 
                ? ArrayHelper::toString($attribs) 
                : (string) $attribs;
            
            $html = '<span ' . trim($attr) . '><i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '"></i></span>';
        } 
        else {
            // Simpele icon font
            $html = '<i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i>';
        }

        return $html;
    }
}