<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * Namespaced controller — delegates to legacy task controller.
 * J6 dispatcher resolves this class; it loads and extends the legacy class
 * which contains the actual task logic.
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Controller;

defined('_JEXEC') or die;

// Load the legacy controller with full task logic
$_legacyFile = JPATH_SITE . '/components/com_planjeagenda/controllers/myevents.php';
if (file_exists($_legacyFile) && !class_exists('PlanjeagendaControllerMyevents', false)) {
    // Load autoloader first so PlanjeagendaControllerForm etc. resolve
    $autoFile = JPATH_SITE . '/components/com_planjeagenda/classes/autoloader.php';
    if (file_exists($autoFile) && !defined('KLEVENTS_AUTOLOADER_LOADED')) {
        require_once $autoFile;
        define('KLEVENTS_AUTOLOADER_LOADED', 1);
    }
    require_once $_legacyFile;
}

/**
 * MyeventsController — J6 namespaced proxy for PlanjeagendaControllerMyevents.
 */
class MyeventsController extends \PlanjeagendaControllerMyevents
{
    // All task methods (save, register, cancel, delete, etc.) are in the
    // legacy parent class. This proxy makes J6's dispatcher find the class.
}
