<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Controller;

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
    public function displayOLDIE($cachable = false, $urlparams = [])
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
            // 1% of requests only — cleanup() has its own once-per-day guard inside.
            if (class_exists('PlanjeagendaHelper', false) && rand(1, 100) === 1) {
                \PlanjeagendaHelper::cleanup();
            }

            $booted = true;
        }

        // Check format — raw/feed/json requests need different view files
        $format   = Factory::getApplication()->input->getCmd('format', 'html');
        $viewExt  = match($format) {
            'raw'  => 'raw',
            'feed' => 'feed',
            'json' => 'json',
            default => 'html',
        };

        // For JSON format: include the view file directly and return
        // (these files output JSON and call exit themselves)
        if ($format === 'json') {
            $jsonFile = JPATH_SITE . '/components/com_planjeagenda/views/'
                      . strtolower($view) . '/view.json.php';
            if (file_exists($jsonFile)) {
                require $jsonFile;
                return; // view.json.php calls exit, but just in case
            }
        }

        // Resolve the view class — prefer namespaced src/View/{View}/HtmlView.php
        $viewTitle    = ucfirst(strtolower($view));
        $nsViewClass  = 'KoelmanLabs\\Component\\Planjeagenda\\Site\\View\\'
                      . $viewTitle . '\\HtmlView';
        $srcViewFile  = JPATH_SITE . '/components/com_planjeagenda/site/src/View/'
                      . $viewTitle . '/HtmlView.php';

        // Legacy fallback
        $viewClass    = 'PlanjeagendaView' . $viewTitle;
        $viewFile     = JPATH_SITE . '/components/com_planjeagenda/views/'
                      . strtolower($view) . '/view.' . $viewExt . '.php';
        if (!file_exists($viewFile)) {
            $viewFile = JPATH_SITE . '/components/com_planjeagenda/views/'
                      . strtolower($view) . '/view.html.php';
        }

        if (file_exists($viewFile) && !class_exists($viewClass, false)) {
            require_once $viewFile;
        }

        if (class_exists($viewClass, false)) {
            // Prepend \ so PHP resolves as global class, not relative to this namespace
            $globalClass = '\\' . ltrim($viewClass, '\\');
            // Pass base_path so PlanjeagendaView finds its templates correctly
            $config = ['base_path' => JPATH_SITE . '/components/com_planjeagenda', 'name' => $view];
            $viewObj  = new $globalClass($config);

            // Wire the namespaced model to the view via MVCFactory
            // Models are in KoelmanLabs\Component\Planjeagenda\Site\Model\{View}Model
            $modelSuffix = ucfirst($view) . 'Model';
            $modelNs     = 'KoelmanLabs\\Component\\Planjeagenda\\Site\\Model\\' . $modelSuffix;

            if ($this->factory && method_exists($this->factory, 'createModel')) {
                try {
                    $modelObj = $this->factory->createModel($modelSuffix, 'KoelmanLabs\\Component\\Planjeagenda\\Site\\Model');
                    if ($modelObj) $viewObj->setModel($modelObj, true);
                } catch (\Throwable $e) {
                    // Fallback to legacy model file
                    $sitePath2  = JPATH_SITE . '/components/com_planjeagenda';
                    $legacyFile = $sitePath2 . '/models/' . strtolower($view) . '.php';
                    $legacyCls  = 'PlanjeagendaModel' . ucfirst($view);
                    if (!class_exists($legacyCls, false) && file_exists($legacyFile)) {
                        require_once $legacyFile;
                    }
                    if (class_exists($legacyCls, false)) {
                        $viewObj->setModel(new $legacyCls(), true);
                    }
                }
            } elseif (class_exists($modelNs, true)) {
                $viewObj->setModel(new $modelNs(), true);
            } else {
                // Legacy fallback
                $sitePath2  = JPATH_SITE . '/components/com_planjeagenda';
                $legacyFile = $sitePath2 . '/models/' . strtolower($view) . '.php';
                $legacyCls  = 'PlanjeagendaModel' . ucfirst($view);
                if (!class_exists($legacyCls, false) && file_exists($legacyFile)) {
                    require_once $legacyFile;
                }
                if (class_exists($legacyCls, false)) {
                    $viewObj->setModel(new $legacyCls(), true);
                }
            }

            $viewObj->display();
        } else {
            // Fallback: load eventslist
            $fallback = JPATH_SITE . '/components/com_planjeagenda/views/eventslist/view.html.php';
            if (file_exists($fallback) && !class_exists('PlanjeagendaViewEventslist', false)) {
                require_once $fallback;
            }
            if (class_exists('PlanjeagendaViewEventslist', false)) {
                $cfg = ['base_path' => JPATH_SITE . '/components/com_planjeagenda', 'name' => 'eventslist'];
                $obj = new \PlanjeagendaViewEventslist($cfg);
                $obj->display();
            }
        }

        return $this;
    }
}
