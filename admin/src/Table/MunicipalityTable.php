<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class MunicipalityTable extends Table
{
	/**
	 * Constructor die verwijst naar de #__pja_municipalities tabel
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__pja_municipalities', 'id', $db);
	}
}