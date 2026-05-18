<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

class MunicipalityModel extends AdminModel
{
	/**
	 * Methode om het formulier te laden
	 */
	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm('com_planjeagenda.municipality', 'municipality', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Laad de bestaande data wanneer een gemeente bewerkt wordt
	 */
	protected function loadFormData()
	{
		$data = $this->getItem();

		return $data;
	}

	/**
	 * Haal de juiste Table klasse op voor de database-afhandeling
	 */
	public function getTable($name = 'Municipality', $prefix = 'Table', $options = [])
	{
		return parent::getTable($name, $prefix, $options);
	}
}