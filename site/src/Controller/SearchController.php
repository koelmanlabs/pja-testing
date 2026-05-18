<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * J6 bridge controller for the search view.
 * Boots all site-side legacy classes, then delegates to
 * DisplayController which renders the legacy PlanjeagendaViewSearch.
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class SearchController extends \Joomla\CMS\MVC\Controller\BaseController
{
    public function execute($task)
    {
        $sitePath = JPATH_SITE . '/components/com_planjeagenda';

        // Boot all site-side global classes needed by the search view and model
        foreach ([
            'PlanjeagendaDebug'        => $sitePath . '/classes/debug.php',
            'PlanjeagendaHelper'       => $sitePath . '/helpers/helper.php',
            'PlanjeagendaConfig'       => $sitePath . '/classes/config.class.php',
            'PlanjeagendaCategories'   => $sitePath . '/classes/categories.class.php',
            'PlanjeagendaHelperRoute'  => $sitePath . '/helpers/route.php',
            'PlanjeagendaMailtoHelper' => $sitePath . '/helpers/mailtohelper.php',
            'PlanjeagendaOutput'       => $sitePath . '/classes/output.class.php',
            'PlanjeagendaAttachment'   => $sitePath . '/classes/attachment.class.php',
            'PlanjeagendaImage'        => $sitePath . '/classes/image.class.php',
            'PlanjeagendaUser'         => $sitePath . '/classes/user.class.php',
            'PlanjeagendaView'         => $sitePath . '/classes/view.class.php',
            'PlanjeagendaFactory'      => $sitePath . '/factory.php',
        ] as $class => $file) {
            if (!class_exists($class, false) && file_exists($file)) {
                require_once $file;
            }
        }

        // Boot the search model so it is available via getModel('search')
        if (!class_exists('PlanjeagendaModelSearch', false)) {
            $file = $sitePath . '/models/search.php';
            if (file_exists($file)) require_once $file;
        }

        // Delegate to DisplayController which handles the legacy view dispatch
        $display = new \DisplayController([], $this->input, $this->app);
        $display->execute('display');
    }
}
