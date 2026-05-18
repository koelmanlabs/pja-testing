<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Municipalities;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $filterForm;
	protected $activeFilters;

	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check op database fouten
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Bouw de Joomla Toolbar knoppen aan de bovenkant van het scherm
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_MUNICIPALITIES_TITLE'), 'location municipality');

		ToolbarHelper::addNew('municipality.add');
		ToolbarHelper::editList('municipality.edit');
		ToolbarHelper::deleteList('COM_PLANJEAGENDA_CONFIRM_DELETE', 'municipalities.delete');
		ToolbarHelper::preferences('com_planjeagenda');
	}
}