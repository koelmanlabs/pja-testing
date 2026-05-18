<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class MunicipalitiesModel extends ListModel
{
	/**
	 * Bouw de query op voor de lijst met gemeenten
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(['id', 'title']))
			->from($db->quoteName('#__pja_municipalities'));

		// Sortering (standaard op titel)
		$query->order($db->quoteName('title') . ' ASC');

		return $query;
	}
}