<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Venues;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper; 

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $lists;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        
        // Data ophalen
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // Foutafhandeling
        if (count($errors = (array)($this->get('Errors') ?? []))) {
            $app->enqueueMessage(implode("\n", $errors), 'error');
            return false;
        }

        // Web Asset Manager (CSS & JS)
        // Web Asset Manager (CSS & JS)
        $wa = $app->getDocument()->getWebAssetManager();
        
        // In plaats van usePreset('jquery'), gebruik je:
        $wa->useScript('jquery');
        
        // Wil je ook dat het script netjes onderaan de pagina geladen wordt?
        // Dan is dit de veiligste manier in Joomla 6:
        $wa->useScript('jquery')
        ->useStyle('fontawesome');

        // Eigen CSS (registreer dit in je joomla.asset.json indien mogelijk)
        // $wa->useStyle('com_planjeagenda.backend');

        // Filter dropdown bouwen (Vervangt de oude $lists)
        $filters = array();
        $filters[] = HTMLHelper::_('select.option', '1', Text::_('COM_PLANJEAGENDA_VENUE'));
        $filters[] = HTMLHelper::_('select.option', '2', Text::_('COM_PLANJEAGENDA_CITY'));
        $filters[] = HTMLHelper::_('select.option', '3', Text::_('COM_PLANJEAGENDA_STATE'));
        $filters[] = HTMLHelper::_('select.option', '4', Text::_('COM_PLANJEAGENDA_COUNTRY'));
        $filters[] = HTMLHelper::_('select.option', '5', Text::_('JALL'));
        
        $this->lists['filter'] = HTMLHelper::_('select.genericlist', $filters, 'filter_type', 
            array('size' => '1', 'class' => 'inputbox form-select'), 
            'value', 'text', $this->state->get('filter_type')
        );

        // Toolbar toevoegen
        $this->addToolbar();
        
        // Haal het filterformulier en de actieve filters op
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        // Controleer op fouten
        if (count($errors = (array)($this->get('Errors') ?? []))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        // Rechten checken op de moderne Joomla manier
        $canDo = ContentHelper::getActions('com_planjeagenda');

        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_VENUES'), 'location');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('venue.add');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('venue.edit');
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::publishList('venues.publish');
            ToolbarHelper::unpublishList('venues.unpublish');
            ToolbarHelper::checkin('venues.checkin');
        }

        if ($canDo->get('core.delete')) {
            ToolbarHelper::deleteList('COM_PLANJEAGENDA_CONFIRM_DELETE', 'venues.delete');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('JHELP_COMPONENTS_PLANJEAGENDA_VENUES');
    }
}