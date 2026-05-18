<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use KoelmanLabs\Component\Planjeagenda\Site\Dispatcher\Dispatcher;
use Psr\Container\ContainerInterface;

class PlanjeagendaComponent extends MVCComponent implements
    BootableExtensionInterface,
    RouterServiceInterface
{
    use HTMLRegistryAwareTrait;
    use RouterServiceTrait;

    public function boot(ContainerInterface $container): void
    {
        $sitePath   = JPATH_SITE . '/components/com_planjeagenda';
        $autoloader = $sitePath . '/classes/autoloader.php';
        if (file_exists($autoloader) && !defined('KLEVENTS_AUTOLOADER_LOADED')) {
            require_once $autoloader;
            define('KLEVENTS_AUTOLOADER_LOADED', 1);
        }
    }

    /**
     * Return our custom dispatcher.
     * Signature matches what Joomla calls: getDispatcher(CMSApplicationInterface $app)
     * Our Dispatcher constructor: __construct($app, $input, $factory)
     */
    public function getDispatcher(CMSApplicationInterface $app): DispatcherInterface
    {
        return new \KoelmanLabs\Component\Planjeagenda\Site\Dispatcher\Dispatcher(
            $app,
            $app->input,
            $this->getMVCFactory()
        );
    }
}
