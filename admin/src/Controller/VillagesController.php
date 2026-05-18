<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class VillagesController extends BaseController
{
	/**
	 * AJAX-methode om dorpen op te halen op basis van municipality_id
	 */
	public function getVillagesByMunicipality()
	{
		// Controleer de token voor veiligheid
		$this->checkToken('get');

		$input          = Factory::getApplication()->input;
		$municipalityId = $input->getInt('municipality_id', 0);
		$db             = Factory::getContainer()->get('DatabaseDriver');

		$query = $db->getQuery(true)
			->select($db->quoteName(['id', 'title']))
			->from($db->quoteName('#__pja_villages'))
			->where($db->quoteName('municipality_id') . ' = ' . $municipalityId)
			->order($db->quoteName('title') . ' ASC');

		$db->setQuery($query);
		$results = $db->loadObjectList() ?: [];

		// Stuur netjes terug als JSON
		echo json_encode($results);
		Factory::getApplication()->close();
	}
}