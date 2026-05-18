<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;

// Can't use JPATH_COMPONENT_SITE because factory maybe used in module or plugin!
require_once (JPATH_SITE.'/components/com_planjeagenda/classes/user.class.php');
require_once (JPATH_SITE.'/components/com_planjeagenda/classes/config.class.php');


/**
 * JEM Factory class
 *
 * @package JEM
 * @since    2.1.5
 */
if (!class_exists('PlanjeagendaFactory')) :
abstract class PlanjeagendaFactory extends Factory
{
    /**
     * Get a JEM user object.
     *
     * Returns the global {@link PlanjeagendaUser} object, only creating it if it doesn't already exist.
     *
     * @param   integer  $id  The user to load - Must be an integer or null for current user.
     *
     * @return  PlanjeagendaUser object
     *
     * @see     PlanjeagendaUser
     * @since   2.1.5
     */
    public static function getUser($id = null)
    {
        $app = Factory::getApplication();
        if (is_null($id))
        {
            $instance = $app->getSession()->get('user');
            $id = ($instance instanceof User) ? $instance->id : 0;
        }

        return PlanjeagendaUser::getInstance($id);
    }

    /**
     * Get the JEM configuration object.
     *
     * Returns the global {@link PlanjeagendaConfig} object, only creating it if it doesn't already exist.
     *
     * @return  PlanjeagendaConfig object
     *
     * @note    Because parent's getConfig() is limited to php files we don't override this function.
     *
     * @see     PlanjeagendaConfig
     * @since   2.1.6
     */
    public static function getPlanjeagendaConfig()
    {
        return PlanjeagendaConfig::getInstance();
    }

    /**
     * Get the dispatcher.
     *
     * Returns the static {@link JDispatcher} or {@link JEventDispatcher} object, depending on Joomla version.
     *
     * @return  JDispatcher or JEventDispatcher object
     *
     * @see     JDispatcher, JEventDispatcher
     * @since   2.1.7
     */
    public static function getDispatcher()
    {
        return Factory::getApplication();
    }
}

endif; // class_exists PlanjeagendaFactory
