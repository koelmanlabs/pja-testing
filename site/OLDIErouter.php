<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Application\CMSApplication;

use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
// use Joomla\CMS\Component\Router\Rules\NomenuRules;
// use Joomla\Component\Planjeagenda\Site\Service\PlanjeagendaNomenuRules as NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;

$_nomenuFile = JPATH_SITE . '/components/com_planjeagenda/services/PlanjeagendaNomenuRules.php';
if (file_exists($_nomenuFile)) {
    require_once $_nomenuFile;
}

class PlanjeagendaRouter extends RouterView
{
    /**
     * Router segments.
     *
     * @var  array
     *
     * @since  1.0.0
     */
    protected $_segments = array();

    /**
     * Router ids.
     *
     * @var  array
     *
     * @since  1.0.0
     */
    protected $_ids = array();

    /**
     * Router constructor.
     *
     * @param   CMSApplication  $app   The application object.
     * @param   AbstractMenu    $menu  The menu object to work with.
     *
     * @since  1.0.0
     */
    public function __construct($app = null, $menu = null)
    {

       
        $viewsWithId = [
            'calendar',
            'eventslist',
            'event',
            'categories',
            'category',
            'attendees',
            'day',
            'editevent',
            'editvenue',
            'myattendances',
            'myevents',
            'myvenues',
            'search',
            'venue',
            'venueslist',
            'venues',
            'weekcal'
        ];

        // Registro masivo de vistas (DRY: Don't Repeat Yourself)
        foreach ($viewsWithId as $viewName) {
            $viewConfig = new RouterViewConfiguration($viewName);
            $viewConfig->setKey('id');
            $this->registerView($viewConfig);
        }

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new PlanjeagendaNomenuRules($this));
    }
}
function jemBuildRoute(&$query)
{
    $app    = Factory::getApplication();

    $router = new PlanjeagendaRouter($app, $app->getMenu());

    return $router->build($query);
}

function jemParseRoute($segments)
{
    $app    = Factory::getApplication();
    $router = new PlanjeagendaRouter($app, $app->getMenu());
    return $router->parse($segments);
}
