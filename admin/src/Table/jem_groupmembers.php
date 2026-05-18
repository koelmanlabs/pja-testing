<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

class jem_groupmembers extends Table
{
    /**
     * Primary Key
     * @var int
     */
    public $id = null;
    /** @var int */
    public $group_id = null;
    /** @var int */
    public $member = null;


    public function __construct($db)
    {
        parent::__construct('#__pja_groupmembers', 'id', $db);
    }
}
