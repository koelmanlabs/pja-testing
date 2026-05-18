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
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
/**
 * View class for the Css-manager screen
 */
class PlanjeagendaViewCssmanager extends PlanjeagendaAdminView
{

    protected $files;

    public function display($tpl = null)
    {
        $this->files = $this->get('Files');
        $this->statusLinenumber = $this->get('StatusLinenumber');

        // Check for errors.
        $errors = $this->get('Errors');
        if (is_array($errors) && count($errors)) {
            Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');
            return false;
        }

        $app = Factory::getApplication();

        // initialise variables
        $this->document = $app->getDocument();
        $user = PlanjeagendaFactory::getUser();

        // Load css
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('planjeagenda.backend');

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('com_planjeagenda_CSSMANAGER_TITLE'), 'thememanager');

        // ToolbarHelper::back();
        ToolbarHelper::custom('cssmanager.back', 'back', 'back', Text::_('com_planjeagenda_ATT_BACK'), false);
        ToolbarHelper::divider();
        ToolbarHelper::inlinehelp();
        ToolBarHelper::help('editcss', true, 'https://www.koelmanlabs.nl/documentation/manual/backend/control-panel/css-manager');
    }
}
