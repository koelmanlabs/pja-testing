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
 * Housekeeping-View
 */
class PlanjeagendaViewHousekeeping extends PlanjeagendaAdminView
{

    public function display($tpl = null) {

        $app = Factory::getApplication();
        $user = $app->getIdentity();

        $this->totalcats = $this->get('Countcats');

        //only admins have access to this view
        if (!$user->authorise('core.manage', 'com_planjeagenda')) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $app->redirect('index.php?option=com_planjeagenda&view=main');
        }

        // Load css
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('planjeagenda.backend');

        // add toolbar
        $this->addToolbar();

        parent::display($tpl);
    }


    /**
     * Add Toolbar
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('com_planjeagenda_HOUSEKEEPING'), 'housekeeping');

        ToolbarHelper::back();
        ToolbarHelper::divider();
        ToolBarHelper::help('housekeeping', true, 'https://www.koelmanlabs.nl/documentation/manual/backend/control-panel/housekeeping');
    }
}
?>
