<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * KL Events — site entry point (Joomla 6 compatible).
 */

defined('_JEXEC') or die;


var_Dump("ik ben hier");exit;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;

$app      = Factory::getApplication();
$sitePath = JPATH_SITE . '/components/com_planjeagenda';
$adminPath = JPATH_ADMINISTRATOR . '/components/com_planjeagenda';

// Register legacy class autoloader
if (!defined('KLEVENTS_AUTOLOADER_LOADED')) {
    require_once $sitePath . '/classes/autoloader.php';
    define('KLEVENTS_AUTOLOADER_LOADED', 1);
}

// Load debug helper
if (!class_exists('PlanjeagendaDebug')) {
    require_once $sitePath . '/classes/debug.php';
}

// Load all shared classes
if (!class_exists('PlanjeagendaFactory'))
    require_once $sitePath . '/factory.php';

if (!class_exists('PlanjeagendaHelper'))
    require_once $sitePath . '/helpers/helper.php';

if (!class_exists('PlanjeagendaMailtoHelper'))
    require_once $sitePath . '/helpers/mailtohelper.php';

if (!class_exists('PlanjeagendaHelperRoute'))
    require_once $sitePath . '/helpers/route.php';

if (!class_exists('PlanjeagendaHelperCountries'))
    require_once $sitePath . '/helpers/countries.php';

if (!class_exists('PlanjeagendaConfig'))
    require_once $sitePath . '/classes/config.class.php';

if (!class_exists('PlanjeagendaUser'))
    require_once $sitePath . '/classes/user.class.php';

if (!class_exists('PlanjeagendaImage'))
    require_once $sitePath . '/classes/image.class.php';

if (!class_exists('PlanjeagendaOutput'))
    require_once $sitePath . '/classes/output.class.php';

if (!class_exists('PlanjeagendaAttachment'))
    require_once $sitePath . '/classes/attachment.class.php';

if (!class_exists('PlanjeagendaCategories'))
    require_once $sitePath . '/classes/categories.class.php';

if (!class_exists('PlanjeagendaView'))
    require_once $sitePath . '/classes/view.class.php';

if (!class_exists('PlanjeagendaCalendar'))
    require_once $sitePath . '/classes/calendar.class.php';

if (!class_exists('PlanjeagendaNomenuRules') && file_exists($sitePath . '/services/PlanjeagendaNomenuRules.php'))
    require_once $sitePath . '/services/PlanjeagendaNomenuRules.php';

// Table path
// Table::addIncludePath removed — PSR-4 handles table loading
// Logger and cleanup
PlanjeagendaHelper::addFileLogger();

// Periodic cleanup (archive, delete, recurrence)
// Guard: only attempt on ~1% of requests to avoid hitting the DB on every page load.
// The cleanup() function itself has a day-boundary check so it only does real work
// once per day — this outer guard just prevents the DB read on every request.
if (rand(1, 100) === 1) {
    PlanjeagendaHelper::cleanup();
}

PlanjeagendaDebug::log('site dispatch', 'view=' . $app->input->getCmd('view', '') . ' task=' . $app->input->getCmd('task', ''));

// Load site controller
if (!class_exists('PlanjeagendaController'))
    require_once $sitePath . '/controller.php';

// Parse task
$task = $app->input->getCmd('task', '');

if (strpos($task, '.') !== false) {
    [$controllerName, $method] = explode('.', $task, 2);
} else {
    $controllerName = '';
    $method = $task ?: 'display';
}

// Load specific controller if needed
if (!empty($controllerName)) {
    $controllerFile = $sitePath . '/controllers/' . strtolower($controllerName) . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
    }
    $controllerClass = 'PlanjeagendaController' . ucfirst($controllerName);
} else {
    $controllerClass = 'PlanjeagendaController';
}

// Instantiate and execute
if (class_exists($controllerClass)) {
    $controller = new $controllerClass(['base_path' => $sitePath]);
} else {
    $controller = new PlanjeagendaController(['base_path' => $sitePath]);
    $method = $task ?: 'display';
}

if (!defined('KLEVENTS_DISPATCHED')) {
    define('KLEVENTS_DISPATCHED', 1);
    $controller->execute($method);

    // Bootstrap assets
    $wa = $app->getDocument()->getWebAssetManager();
    $wa->useScript('jquery');

    $controller->redirect();
}
