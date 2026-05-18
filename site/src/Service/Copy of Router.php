<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Service;


defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;

// Load the NomenuRules if available
$_nomenuFile = JPATH_SITE . '/components/com_planjeagenda/services/PlanjeagendaNomenuRules.php';
if (file_exists($_nomenuFile) && !class_exists('PlanjeagendaNomenuRules')) {
    require_once $_nomenuFile;
}

/**
 * Routing class for com_planjeagenda (J6 namespaced entry point).
 * 
 * The J6 RouterFactory resolves this class via PSR-4:
 *   KoelmanLabs\Component\Planjeagenda\Site\Service\Router
 *
 * It delegates to the legacy PlanjeagendaRouter which is already fully
 * functional (RouterView + NomenuRules).
 */
class Router extends RouterView
{
    /**
     * Views that accept an id segment.
     */
    private const VIEWS_WITH_ID = [
        'calendar', 'eventslist', 'event', 'categories', 'category',
        'attendees', 'day', 'editevent', 'editvenue', 'myattendances',
        'myevents', 'myvenues', 'search', 'venue', 'venueslist',
        'venues', 'weekcal',
    ];

    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {
        foreach (self::VIEWS_WITH_ID as $viewName) {
            $viewConfig = new \Joomla\CMS\Component\Router\RouterViewConfiguration($viewName);
            $viewConfig->setKey('id');
            $this->registerView($viewConfig);
        }

        parent::__construct($app, $menu);

        $this->attachRule(new \Joomla\CMS\Component\Router\Rules\MenuRules($this));
        $this->attachRule(new \Joomla\CMS\Component\Router\Rules\StandardRules($this));

        // Attach NomenuRules if the class was loaded
        if (class_exists('PlanjeagendaNomenuRules')) {
            $this->attachRule(new \PlanjeagendaNomenuRules($this));
        }
    }
}
