<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Municipality;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
	protected $form;
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Knoppen voor het formulier (Opslaan, Sluiten)
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->id == 0);
		
		ToolbarHelper::title($isNew ? Text::_('COM_PLANJEAGENDA_MUNICIPALITY_NEW') : Text::_('COM_PLANJEAGENDA_MUNICIPALITY_EDIT'), 'location municipality');

		ToolbarHelper::apply('municipality.apply');
		ToolbarHelper::save('municipality.save');
		ToolbarHelper::cancel('municipality.cancel', 'municipality.cancel');
	}
}