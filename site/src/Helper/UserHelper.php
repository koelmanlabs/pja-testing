<?php

/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;

/**
 * Extended Planjeagenda User Entity
 */
class UserHelper extends User
{
    /**
     * @var array Static instances cache
     */
    protected static $instances = [];

    /**
     * Factory method to securely pull a custom cached instance of this extended class layout
     *
     * @param   int  $id  User ID
     * @return  UserHelper
     */
    public static function getInstance($id = 0)
    {
        $id = (int) $id;

        if (empty(self::$instances[$id])) {
            // Instantiate this specific class extension cleanly
            $instance = new self();
            if ($id > 0) {
                $instance->load($id);
            }
            self::$instances[$id] = $instance;
        }

        return self::$instances[$id];
    }

    /**
     * Returns all JEM groups user is member of.
     *
     * @param   mixed  $asset  false, string or array of strings
     * @return  array
     */
    public function getJemGroups($asset = false)
    {
        $userId = (int) $this->id;

        if (empty($userId)) {
            return [];
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        if (is_array($asset) && !empty($asset)) {
            array_walk($asset, function(&$v, $k, $db) {
                $v = $db->quoteName($v);
            }, $db);
            $field = ' AND (' . implode(' > 0 OR ', $asset) . ' > 0)';
        } else {
            $field = empty($asset) ? '' : ' AND ' . $db->quoteName($asset) . ' > 0';
        }

        $query = $db->getQuery(true)
            ->select('gr.*')
            ->from('#__pja_groups AS gr')
            ->leftJoin('#__pja_groupmembers AS gm ON gm.group_id = gr.id')
            ->where('gm.member = ' . $userId)
            ->where($field)
            ->group('gr.id');

        $db->setQuery($query);

        return $db->loadAssocList('id') ?: [];
    }

    // ==================== Overige methodes ====================

    public function validate_user($recurse, $level)
    {
        if ($this->id) {
            if ((($level == -1) && $this->id) || (($level == -2) && $this->authorise('core.manage'))) {
                return true;
            }
        }
        return false;
    }

    public function editaccess($allowowner, $ownerid, $recurse, $level)
    {
        $generalaccess = $this->validate_user($recurse, $level);

        if ((($allowowner == 1) || $this->authorise('core.edit.own', 'com_planjeagenda')) 
            && ($this->id == $ownerid) && ($ownerid != 0)) {
            return true;
        } elseif (($generalaccess == 1) || $this->authorise('core.edit', 'com_planjeagenda')) {
            return true;
        }
        return false;
    }

    public function venuegroups($action)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('gr.id')
            ->from('#__pja_groups AS gr')
            ->leftJoin('#__pja_groupmembers AS g ON g.group_id = gr.id')
            ->where('g.member = ' . (int) $this->id)
            ->where($db->quoteName('gr.' . $action . 'venue') . ' = 1')
            ->where('g.member != 0');

        $db->setQuery($query);
        return (bool) $db->loadResult();
    }
}