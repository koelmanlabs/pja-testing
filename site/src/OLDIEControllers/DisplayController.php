<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Controllers;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

/**
 * Site Display Controller.
 *
 * In J6, the ComponentDispatcher routes frontend requests through this controller.
 * For com_planjeagenda, the actual display logic lives in the legacy klevents.php
 * entry point which handles the full old-style MVC dispatch. This controller
 * acts as the J6-compatible bridge entry point.
 */
class DisplayController extends BaseController
{
    /**
     * The default view to show.
     */
    protected $default_view = 'eventslist';

    /**
     * Display the view.
     */
    public function displayOLD($cachable = false, $urlparams = [])
    {
        $app  = Factory::getApplication();
        $view = $app->input->getCmd('view', $this->default_view);

        // Load legacy site-side classes (once per request)
        static $booted = false;
        if (!$booted) {
            $sitePath = JPATH_SITE . '/components/com_planjeagenda';

            foreach ([
                'PlanjeagendaHelper'       => $sitePath . '/helpers/helper.php',
                'PlanjeagendaOutput'       => $sitePath . '/classes/output.class.php',
                'PlanjeagendaCategories'   => $sitePath . '/classes/categories.class.php',
                'PlanjeagendaConfig'       => $sitePath . '/classes/config.class.php',
                'PlanjeagendaUser'         => $sitePath . '/classes/user.class.php',
                'PlanjeagendaImage'        => $sitePath . '/classes/image.class.php',
                'PlanjeagendaAttachment'   => $sitePath . '/classes/attachment.class.php',
                'PlanjeagendaView'         => $sitePath . '/classes/view.class.php',
                'PlanjeagendaCalendar'     => $sitePath . '/classes/calendar.class.php',
                'PlanjeagendaFactory'      => $sitePath . '/factory.php',
                'PlanjeagendaHelperRoute'  => $sitePath . '/helpers/route.php',
                'PlanjeagendaMailtoHelper' => $sitePath . '/helpers/mailtohelper.php',
            ] as $class => $file) {
                if (!class_exists($class, false) && file_exists($file)) {
                    require_once $file;
                }
            }

            // Periodic cleanup (recurrence, archive, delete)
            if (class_exists('PlanjeagendaHelper', false)) {
                \PlanjeagendaHelper::cleanup();
            }

            $booted = true;
        }

        // Delegate to the legacy view dispatcher
        $viewClass = 'PlanjeagendaView' . ucfirst($view);
        $viewFile  = JPATH_SITE . '/components/com_planjeagenda/views/' . $view . '/view.html.php';

        if (file_exists($viewFile) && !class_exists($viewClass)) {
            require_once $viewFile;
        }

        if (class_exists($viewClass)) {
            $viewObj = new $viewClass();
            $viewObj->display();
        } else {
            // Fallback: load eventslist
            $fallback = JPATH_SITE . '/components/com_planjeagenda/views/eventslist/view.html.php';
            if (file_exists($fallback)) {
                require_once $fallback;
                $obj = new PlanjeagendaViewEventslist();
                $obj->display();
            }
        }

        return $this;
    }
}
