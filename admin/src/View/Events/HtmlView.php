<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Events;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaConfig;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

/**
 * Events-View klasse
 */
class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $lists;
    
    protected $settingsConfig; // config.xml
    protected $settingsTable; // Table

    /**
     * Display de view
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        
        // 1. Haal data op uit de Model
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // Veiligheidscheck: Zorg dat state een object is voor de template
        if ($this->state === null) {
            $model = $this->getModel();
            $this->state = $model ? $model->getState() : new \Joomla\Registry\Registry();
        }
        
        
        
        $model = $app->bootComponent('com_planjeagenda')
        ->getMVCFactory()
        ->createModel('Events', 'Administrator', ['ignore_request' => false]);
        
        $this->municipalities = $model->getMunicipalities();
        
        // VEILIGHEIDSCHECK: Als pagination null is, maak een leeg object aan om de crash te voorkomen
        if ($this->pagination === null) {
            $this->pagination = new \Joomla\CMS\Pagination\Pagination(0, 0, 0);
        }

        // Haal instellingen op
        // Convert to stdClass so templates can use ->property syntax (not just ->get())
        $this->settingsConfig   = ComponentHelper::getParams('com_planjeagenda');
        $this->settingsTable    = PlanjeagendaConfig::getInstance()->toObject();

        // Controleer op fouten
        $errors = $this->get('Errors');
        if (is_array($errors) && count($errors)) {
            $app->enqueueMessage(implode("\n", $errors), 'error');
            return false;
        }
           
        $wa = $this->document->getWebAssetManager();
        $wa->registerAndUseStyle('com_planjeagenda.events', 'com_planjeagenda/backendevents.css');

        // Toolbar instellen
        $this->addToolbar();
        
        // Haal het filterformulier en de actieve filters op
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
     
        return parent::display($tpl);
    }

    /**
     * Toolbar configuratie
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('com_planjeagenda_EVENTS'), 'calendar');

        $canDo      = ContentHelper::getActions('com_planjeagenda');
        $filterState = (int) ($this->state?->get('filter_state') ?? '');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('event.add');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('event.edit');
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::publishList('events.publish');
            ToolbarHelper::unpublishList('events.unpublish');
            ToolbarHelper::archiveList('events.archive');
            ToolbarHelper::checkin('events.checkin');
        }

        // Toon "Prullenbak leegmaken" als we in de prullenbak-view zitten,
        // anders de gewone verplaats-naar-prullenbak knop.
        if ($filterState === -2 && $canDo->get('core.delete')) {
            ToolbarHelper::deleteList(
                Text::_('com_planjeagenda_CONFIRM_DELETE'),
                'events.delete',
                'JTOOLBAR_EMPTY_TRASH'
            );
        } elseif ($canDo->get('core.edit.state')) {
            ToolbarHelper::trash('events.trash');
        }

        ToolbarHelper::divider();
        ToolbarHelper::preferences('com_planjeagenda');
        /* ToolbarHelper::help('listevents'); */
    }
}