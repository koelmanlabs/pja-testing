<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Site dispatcher for com_planjeagenda.
 *
 * Constructor matches Joomla\CMS\Dispatcher\AbstractDispatcher:
 *   __construct(CMSApplicationInterface $app, Input $input, MVCFactoryInterface $factory)
 */
class Dispatcher implements DispatcherInterface
{
    private CMSApplicationInterface $app;
    private Input $input;
    private ?MVCFactoryInterface $factory;

    public function __construct(
        CMSApplicationInterface $app,
        Input $input,
        ?MVCFactoryInterface $factory = null
    ) {
        $this->app     = $app;
        $this->input   = $input;
        $this->factory = $factory;
    }

    public function dispatch(): void
    {
        $task     = $this->input->getCmd('task', '');
        $sitePath = JPATH_SITE . '/components/com_planjeagenda';

        // Ensure legacy autoloader is ready
        if (!defined('KLEVENTS_AUTOLOADER_LOADED')
            && file_exists($sitePath . '/classes/autoloader.php')) {
            require_once $sitePath . '/classes/autoloader.php';
            define('KLEVENTS_AUTOLOADER_LOADED', 1);
        }

        // Split dotted task into controller.method
        if (strpos($task, '.') !== false) {
            [$controllerName, $method] = explode('.', $task, 2);
        } else {
            $controllerName = '';
            $method = $task ?: 'display';
        }

        // Try legacy task controller first (event, attendees, venue, etc.)
        if ($controllerName) {
            $file        = $sitePath . '/controllers/' . strtolower($controllerName) . '.php';
            $legacyClass = 'PlanjeagendaController' . ucfirst($controllerName);

            if (!class_exists($legacyClass, false) && file_exists($file)) {
                require_once $file;
            }

            if (class_exists($legacyClass, false)) {
                $controller = new $legacyClass(['base_path' => $sitePath]);
                $controller->execute($method);
                $controller->redirect();
                return;
            }
        }

        // All display requests → DisplayController
        $controller = new \KoelmanLabs\Component\Planjeagenda\Site\Controller\DisplayController(
            [],
            $this->factory,
            $this->app,
            $this->input
        );
        $controller->execute('display');
        $controller->redirect();
    }
}
