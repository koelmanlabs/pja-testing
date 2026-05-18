<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

/**
 * View class for the JEM Groups screen
 *
 * @package Joomla
 * @subpackage JEM
 *
 */

class PlanjeagendaViewGroups extends PlanjeagendaAdminView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        $user        = PlanjeagendaFactory::getUser();
        $jemsettings = PlanjeagendaAdmin::config();

        // Initialise variables.
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // Load css
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('planjeagenda.backend');

        // assign data to template
        $this->user            = $user;
        $this->jemsettings  = $jemsettings;

        // add toolbar
        $this->addToolbar();

        parent::display($tpl);
        }


    /**
     * Add Toolbar
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('com_planjeagenda_GROUPS'), 'groups');

        /* retrieving the allowed actions for the user */
        $canDo = PlanjeagendaHelperBackend::getActions(0);

        /* create */
        if (($canDo->get('core.create'))) {
            ToolbarHelper::addNew('group.add');
        }

        /* edit */
        if (($canDo->get('core.edit'))) {
            ToolbarHelper::editList('group.edit');
            ToolbarHelper::divider();
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::checkin('groups.checkin');
        }

        ToolbarHelper::deleteList('com_planjeagenda_CONFIRM_DELETE', 'groups.remove', 'JACTION_DELETE');

        ToolbarHelper::divider();
        ToolBarHelper::help('listgroups', true, 'https://www.koelmanlabs.nl/documentation/manual/backend/groups');
    }
}
?>
