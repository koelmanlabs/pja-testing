<?php
defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
// Hier laden we jouw specifieke class
use KoelmanLabs\Component\Planjeagenda\Administrator\Extension\PlanjeagendaComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container)
    {
        $container->registerServiceProvider(new CategoryFactory('\\KoelmanLabs\\Component\\Planjeagenda'));
        $container->registerServiceProvider(new MVCFactory('\\KoelmanLabs\\Component\\Planjeagenda'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\KoelmanLabs\\Component\\Planjeagenda'));
        $container->registerServiceProvider(new RouterFactory('\\KoelmanLabs\\Component\\Planjeagenda'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                // Let op: Geen backslash voor PlanjeagendaComponent omdat we bovenin 'use' gebruiken
                $component = new PlanjeagendaComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};