<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

/**
 * Planjeagenda attachments table class
 */
class AttachmentTable extends Table
{
    /**
     * @param   DatabaseInterface  $db  Database connector object
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__pja_attachments', 'id', $db);
    }

    /**
     * Overloaded bind function
     * Hiermee zorgen we dat data netjes wordt gekoppeld
     */
    public function bind($array, $ignore = '')
    {
        return parent::bind($array, $ignore);
    }

    /**
     * Overloaded check function
     */
    public function check()
    {
        // Valideer of de bestandsnaam niet leeg is
        if (empty($this->file)) {
            $this->setError('Bestandsnaam mag niet leeg zijn.');
            return false;
        }

        return true;
    }
}