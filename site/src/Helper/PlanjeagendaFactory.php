<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\Helper\UsersHelper;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaConfig;


// LET OP: Deze paden moeten kloppen. Als je ze nog niet hebt verplaatst, laat ze hier naar de frontend wijzen.
// require_once (JPATH_SITE . '/components/com_planjeagenda/classes/user.class.php');
// require_once (JPATH_SITE . '/components/com_planjeagenda/classes/config.class.php');

/**
 * Planjeagenda Factory class
 */
abstract class PlanjeagendaFactory extends Factory
{
    /**
     * Get a Planjeagenda user object.
     */
    public static function getUser($id = null)
    {
        $app = Factory::getApplication();
        if (is_null($id))
        {
            // In J6 gebruiken we getIdentity() voor de huidige user
            $instance = $app->getIdentity();
            $id = ($instance instanceof User) ? $instance->id : 0;
        }

        // PlanjeagendaUser moet waarschijnlijk nog globaal beschikbaar zijn of ook een namespace krijgen
        return UserHelper::getInstance($id);
    }

    /**
     * Get the Planjeagenda configuration object.
     */
    public static function getPlanjeagendaConfig()
    {
        return \PlanjeagendaConfig::getInstance();
    }

    /**
     * Get the dispatcher. (In J6 is de Application de dispatcher)
     */
    public static function getDispatcher()
    {
        return Factory::getApplication();
    }
}