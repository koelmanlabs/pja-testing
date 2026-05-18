<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Updatecheck;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * View class for the Planjeagenda Updatecheck screen
 */
class HtmlView extends BaseHtmlView
{
    protected $app;
    public $updatedata;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->app = Factory::getApplication();
    }

    public function display($tpl = null)
    {
        // Haal de data op uit het UpdatecheckModel
        $this->updatedata = $this->get('Updatedata');

        // Check of data succesvol is opgehaald (let op de $this->)
        if ($this->updatedata === false) {
            $this->app->enqueueMessage(Text::_('COM_PLANJEAGENDA_ERROR_UPDATEDATA'), 'warning');
            $this->updatedata = new \stdClass(); // Backslash toegevoegd
        }

        // Assets laden
        $this->loadCss();

        // Toolbar instellen
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Load CSS assets via WebAssetManager
     */
    protected function loadCss()
    {
        $wa = $this->app->getDocument()->getWebAssetManager();

        // Register de stijl. Zorg dat het bestand fysiek staat in:
        // media/com_planjeagenda/css/backend.css
        if (!$wa->assetExists('style', 'planjeagenda.backend')) {
            $wa->registerAndUseStyle('planjeagenda.backend', 'com_planjeagenda/backend.css');
        } else {
            $wa->useStyle('planjeagenda.backend');
        }
    }

    /**
     * Add Toolbar
     */
    protected function addToolbar()
    {
        // Titel van de pagina
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_UPDATECHECK_TITLE'), 'loop');
        
        ToolbarHelper::divider();
        
        $bar = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
        $bar->appendButton('Link', 'cancel', 'JTOOLBAR_CLOSE', 'index.php?option=com_planjeagenda&view=main');
    }
}