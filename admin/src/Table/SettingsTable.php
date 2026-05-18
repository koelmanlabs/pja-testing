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
use Joomla\Registry\Registry;

class SettingsTable extends Table
{
    public function __construct($db)
    {
        parent::__construct('#__pja_settings', 'id', $db);
    }

    /**
     * Validators
     * @deprecated since version 2.1.6
     */
    public function check()
    {
        return true;
    }

    /**
     * Overloaded the store method
     * @deprecated since version 2.1.6
     */
    public function store($updateNulls = false)
    {
        return parent::store($updateNulls);
    }

    /**
     * @deprecated since version 2.1.6
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['globalattribs']) && is_array($array['globalattribs']))
        {
            $registry = new Registry;
            $registry->loadArray($array['globalattribs']);
            $array['globalattribs'] = (string) $registry;
        }

        if (isset($array['css']) && is_array($array['css']))
        {
            $registrycss = new Registry;
            $registrycss->loadArray($array['css']);
            $array['css'] = (string) $registrycss;
        }

        //don't override without calling base class
        return parent::bind($array, $ignore);
    }
}
