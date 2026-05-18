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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Client\ClientHelper;

/**
 * Source view
 *
 */
class PlanjeagendaViewSource extends PlanjeagendaAdminView
{
    protected $form;
    protected $ftp;
    protected $source;
    protected $state;
    protected $template;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        // Initialise variables.
        $this->form     = $this->get('Form');
        $this->ftp      = ClientHelper::setCredentialsFromRequest('ftp');
        $this->source   = $this->get('Source');
        $this->state    = $this->get('State');
        $this->template = $this->get('Template');

        // Check for errors.
        $errors = $this->get('Errors');
        if (is_array($errors) && count($errors)) {
            \Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');
            return false;
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user  = PlanjeagendaFactory::getUser();
        $canDo = PlanjeagendaHelperBackend::getActions(0);

        ToolbarHelper::title(Text::_('com_planjeagenda_CSSMANAGER_EDIT_FILE'), 'thememanager');

        // Can save the item.
        if ($canDo->get('core.edit')) {
            ToolbarHelper::apply('source.apply');
            ToolbarHelper::save('source.save');
        }

        ToolbarHelper::cancel('source.cancel', 'JTOOLBAR_CLOSE');
        ToolbarHelper::divider();
        ToolBarHelper::help('editcss', true, 'https://www.koelmanlabs.nl/documentation/manual/backend/control-panel/css-manager/edit-file');
    }
}
