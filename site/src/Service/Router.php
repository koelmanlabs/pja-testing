<?php
namespace KoelmanLabs\Component\Planjeagenda\Site\Service;

defined('_JEXEC') || die;

use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\Categories;


/**
 * Planjeagenda Component Router Service
 */
class Router extends RouterView
{
    /**
     * Component router constructor
     *
     * @param  \Joomla\CMS\Application\SiteApplication  $app         The site application object
     * @param  \Joomla\CMS\Menu\AbstractMenu            $menu        The menu object
     * @param  \Joomla\CMS\Categories\Categories        $categories  The categories object (optional)
     */
    /**
     * Component router constructor
     *
     * @param  \Joomla\CMS\Application\SiteApplication  $app   The site application object
     * @param  \Joomla\CMS\Menu\AbstractMenu            $menu  The menu object
     */
    public function __construct(\Joomla\CMS\Application\SiteApplication $app, \Joomla\CMS\Menu\AbstractMenu $menu)
    {
        // Pass the core requirements directly up to the parent RouterView
        parent::__construct($app, $menu);

        // 1. Setup the collection / index view list
        $eventslist = new RouterViewConfiguration('eventslist');
        $eventslist->setKey('id');
        $this->registerView($eventslist);

        // 2. Setup the single detail item view rule 
        $event = new RouterViewConfiguration('event');
        $event->setKey('id')
            ->setParent($eventslist, 'catid')
            ->setNestable();
        $this->registerView($event);
        
        // 3. Setup Venues/Locations subviews if you use a venue profile view
        $venue = new RouterViewConfiguration('venue');
        $venue->setKey('id');
        $this->registerView($venue);
    }

    /**
     * Custom route construction to build aliases seamlessly 
     */
    protected function getEventId($segment, $query)
    {
        return (int) $segment;
    }

    protected function getEventslistId($segment, $query)
    {
        return (int) $segment;
    }
    
    protected function getVenueId($segment, $query)
    {
        return (int) $segment;
    }
}