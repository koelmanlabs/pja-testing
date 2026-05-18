<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\HTML\Helpers\Sidebar;

require_once(JPATH_SITE.'/components/com_planjeagenda/factory.php');


// class PlanjeagendaSidebarHelper (Sidebar removed in J6)
class PlanjeagendaSidebarHelper
{
    public static function addEntry($name, $link = '', $active = false): void
    {
        // Stub — Joomla\CMS\HTML\Helpers\Sidebar removed in J6
        // Submenu entries are currently non-functional via this method
    }

    public static function render(): string
    {
        return '';
    }

    public static function getEntries(): array
    {
        return [];
    }
}


/**
 * Helper: Backend
 */
class PlanjeagendaHelperBackend
{

    public static $extension = 'com_planjeagenda';

    /**
     * Configure the Linkbar.
     *
     * @param    string    The name of the active view.
     *
     * @return    void
     *
     */
    public static function addSubmenu($vName)
    {
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

        PlanjeagendaSidebarHelper::addEntry(
            Text::_('com_planjeagenda_GROUPS'),
            'index.php?option=com_planjeagenda&view=groups',
            $vName == 'groups'
        );

        if (PlanjeagendaFactory::getUser()->authorise('core.manage', 'com_planjeagenda')) {
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

    /**
     * Gets a list of the actions that can be performed.
     *
     * @param    int        The category ID.
     *
     * @return    CMSObject
     */
    public static function getActions($categoryId = 0)
    {
        $user    = PlanjeagendaFactory::getUser();
        $result    = new CMSObject;;

        if (empty($categoryId)) {
            $assetName = 'com_planjeagenda';
            $level = 'component';
        } else {
            $assetName = 'com_planjeagenda.category.'.(int) $categoryId;
            $level = 'category';
        }

        // $actions = Access::getActions('com_planjeagenda', $level);
        $actions = Access::getActionsFromFile(JPATH_ADMINISTRATOR.'/components/com_planjeagenda/access.xml',"/access/section[@name='".$level."']/");

        foreach ($actions as $action) {
            $result->set($action->name,    $user->authorise($action->name, $assetName));
        }

        return $result;
    }

    public static function getCountryOptions()
    {
        $options = array();
        $options = array_merge(PlanjeagendaHelperCountries::getCountryOptions(),$options);

        array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('com_planjeagenda_SELECT_COUNTRY')));

        return $options;
    }

}
