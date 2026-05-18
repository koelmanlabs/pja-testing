<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use KoelmanLabs\Component\Planjeagenda\Site\Dispatcher\Dispatcher;
use KoelmanLabs\Component\Planjeagenda\Site\Extension\PlanjeagendaComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\KoelmanLabs\\Component\\Planjeagenda'));
        $container->registerServiceProvider(new RouterFactory('\\KoelmanLabs\\Component\\Planjeagenda'));

        // Register our custom dispatcher factory — bypasses J6's default
        // which tries to resolve namespaced controllers by view name
        $container->set(
            ComponentDispatcherFactoryInterface::class,
            static function (Container $container) {
                return new class ($container) implements ComponentDispatcherFactoryInterface {
                    private Container $container;
                    public function __construct(Container $c) { $this->container = $c; }
                    public function createDispatcher(CMSApplicationInterface $app): DispatcherInterface {
                        return new Dispatcher(
                            $app,
                            $app->input,
                            $this->container->get(MVCFactoryInterface::class)
                        );
                    }
                };
            }
        );

        $container->set(
            ComponentInterface::class,
            static function (Container $container) {
                $component = new PlanjeagendaComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));
                return $component;
            }
        );
    }
};
