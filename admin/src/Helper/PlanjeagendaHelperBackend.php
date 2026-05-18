<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaSidebarHelper;

// Gebruik de factory via de juiste namespace (indien al aangepast)
// use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaFactory;

/**
 * Helper: Backend
 */
class PlanjeagendaHelperBackend
{
    public static $extension = 'com_planjeagenda';

    /**
     * Configure the Linkbar.
     * In J6 doen we dit meestal via de preset in de menu-xml of provider, 
     * maar als overgangslossing houden we deze functie aan.
     */
    public static function addSubmenu($vName)
    {
        // We roepen de SidebarHelper aan (zie hieronder)
        PlanjeagendaSidebarHelper::addEntry(
            Text::_('com_planjeagenda_SUBMENU_MAIN'),
            'index.php?option=com_planjeagenda&view=main',
            $vName == 'main'
        );
        
        PlanjeagendaSidebarHelper::addEntry(
            Text::_('com_planjeagenda_EVENTS'),
            'index.php?option=com_planjeagenda&view=events',
            $vName == 'events'
            );
        
        PlanjeagendaSidebarHelper::addEntry(
            Text::_('com_planjeagenda_VENUES'),
            'index.php?option=com_planjeagenda&view=venues',
            $vName == 'venues'
            );
        
        PlanjeagendaSidebarHelper::addEntry(
            Text::_('com_planjeagenda_CATEGORIES'),
            'index.php?option=com_planjeagenda&view=categories',
            $vName == 'categories'
            );
        
        
        \Joomla\CMS\Helper\ContentHelper::addSubmenu(
            Text::_('COM_PLANJEAGENDA_SUBMENU_MUNICIPALITIES'),
            'index.php?option=com_planjeagenda&view=municipalities',
            $vName == 'municipalities'
            );
        
        
        if (\Joomla\CMS\Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_planjeagenda')) {
            PlanjeagendaSidebarHelper::addEntry(
                Text::_('com_planjeagenda_SETTINGS_TITLE'),
                'index.php?option=com_planjeagenda&view=settings',
                $vName == 'settings'
                );
            
            PlanjeagendaSidebarHelper::addEntry(
                Text::_('com_planjeagenda_HOUSEKEEPING'),
                'index.php?option=com_planjeagenda&amp;view=housekeeping',
                $vName == 'housekeeping'
                );
            
            PlanjeagendaSidebarHelper::addEntry(
                Text::_('com_planjeagenda_UPDATECHECK_TITLE'),
                'index.php?option=com_planjeagenda&amp;view=updatecheck',
                $vName == 'updatecheck'
                );
            
            PlanjeagendaSidebarHelper::addEntry(
                Text::_('com_planjeagenda_IMPORT_DATA'),
                'index.php?option=com_planjeagenda&amp;view=import',
                $vName == 'import'
                );
            
            PlanjeagendaSidebarHelper::addEntry(
                Text::_('com_planjeagenda_EXPORT_DATA'),
                'index.php?option=com_planjeagenda&amp;view=export',
                $vName == 'export'
                );
            
            PlanjeagendaSidebarHelper::addEntry(
                Text::_('com_planjeagenda_CSSMANAGER_TITLE'),
                'index.php?option=com_planjeagenda&amp;view=cssmanager',
                $vName == 'cssmanager'
                );
        }
        
        PlanjeagendaSidebarHelper::addEntry(
            Text::_('com_planjeagenda_DEBUG_LOG_TITLE'),
            'index.php?option=com_planjeagenda&view=debuglog',
            $vName == 'debuglog'
            );
        
        PlanjeagendaSidebarHelper::addEntry(
            Text::_('com_planjeagenda_HELP'),
            'index.php?option=com_planjeagenda&view=help',
            $vName == 'help'
            );
    }

    public static function getActions($categoryId = 0)
    {
        // Gebruik de moderne manier om de user op te halen in plaats van de oude Factory 
        $user = \Joomla\CMS\Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        $level = empty($categoryId) ? 'component' : 'category';
        $assetName = empty($categoryId) ? 'com_planjeagenda' : 'com_planjeagenda.category.' . (int) $categoryId;

        $actions = Access::getActionsFromFile(
            JPATH_ADMINISTRATOR . '/components/com_planjeagenda/access.xml',
            "/access/section[@name='" . $level . "']/"
        );

        foreach ($actions as $action) {
            $result->set($action->name, $user->authorise($action->name, $assetName));
        }

        return $result;
    }

    /**
     * Get country options - delegates to PlanjeagendaHelper.
     */
    public static function getCountryOptions(): array
    {
        return \KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper::getCountryOptions();
    }

}