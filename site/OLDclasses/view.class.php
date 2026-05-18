<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * PlanjeagendaView class with JEM specific extensions
 *
 * @package JEM
 */
class PlanjeagendaView extends HtmlView
{
    /**
     * Layout style suffix
     *
     * @var    string
     * @since  2.3
     */
    protected $_layoutStyleSuffix = null;

    /**
     * Get or instantiate the model for this view.
     * In J6 with legacy views, getModel() from HtmlView may return null
     * because the model isn't injected by MVCFactory. This override
     * falls back to direct instantiation of the matching site model.
     */
    public function getModel($name = null, $prefix = 'PlanjeagendaModel', $config = [])
    {
        // Derive view name FIRST to avoid passing null to parent::getModel()
        // which calls strtolower($name) — causes deprecation in J6
        $viewName = $name ?: ($this->getName() ?? '');

        // Only call parent if the model is already registered — avoids
        // "Undefined array key" warning from AbstractView::getModel()
        if (!empty($viewName) && isset($this->_models[strtolower($viewName)])) {
            $model = parent::getModel($viewName, $prefix, $config);
            if ($model) {
                return $model;
            }
        }

        if (empty($viewName)) {
            return null;
        }

        // Try namespaced model first (src/Model/)
        $nsModel = 'KoelmanLabs\\Component\\Planjeagenda\\Site\\Model\\'
                 . ucfirst($viewName) . 'Model';
        if (class_exists($nsModel, true)) {
            $model = new $nsModel($config);
            $this->setModel($model, true);
            return $model;
        }

        // Fallback: legacy models/ folder
        $modelName = $prefix . ucfirst($viewName);
        if (!class_exists($modelName, false)) {
            $modelFile = JPATH_SITE . '/components/com_planjeagenda/models/'
                       . strtolower($viewName) . '.php';
            if (file_exists($modelFile)) {
                require_once $modelFile;
            }
        }
        if (class_exists($modelName, false)) {
            $model = new $modelName($config);
            $this->setModel($model, true);
            return $model;
        }

        return null;
    }

    /**
     * Override get() to ensure the model is available even when
     * AbstractView's default model lookup fails in J6.
     */
    public function get($property, $default = null)
    {
        // Try standard AbstractView::get() first
        try {
            $result = parent::get($property, $default);
            if ($result !== null) {
                return $result;
            }
        } catch (\Throwable $e) {
            // Fall through to direct model call
        }

        // Fallback: call directly on our model
        $model = $this->getModel();
        if ($model && method_exists($model, 'get' . ucfirst($property))) {
            return $model->{'get' . ucfirst($property)}();
        }

        return $default;
    }

    public function __construct($config = array())
    {
        // If template_path is provided (from DisplayController), pre-add those paths
        if (!empty($config['template_path'])) {
            foreach ((array)$config['template_path'] as $p) {
                if (is_dir($p) && !isset($config['template'])) {
                    // Will be added after parent::__construct
                }
            }
        }

        parent::__construct($config);

        // Add src/View/{View}/tmpl/ paths (J6 location — highest priority)
        $viewTitle = ucfirst($this->getName());
        $srcBase   = JPATH_SITE . '/components/com_planjeagenda/site/src/View/' . $viewTitle . '/tmpl';
        if (is_dir($srcBase)) {
            $this->addTemplatePath($srcBase);
        }

        // Additional path for layout style suffix (responsive etc.)
        $suffix = \PlanjeagendaHelper::getLayoutStyleSuffix();
        if (!empty($suffix)) {
            $this->_layoutStyleSuffix = $suffix;

            // src/View path with suffix
            if (is_dir($srcBase . '/' . $suffix)) {
                $this->addTemplatePath($srcBase . '/' . $suffix);
            }

            // Legacy views path with suffix
            if (is_dir($this->_basePath . '/view')) {
                $this->addTemplatePath($this->_basePath . '/view/' . $this->getName() . '/tmpl/' . $suffix);
            } else {
                $this->addTemplatePath($this->_basePath . '/views/' . $this->getName() . '/tmpl/' . $suffix);
            }
            $this->addTemplatePath(JPATH_THEMES . '/' . Factory::getApplication()->getTemplate() . '/html/com_planjeagenda/' . $this->getName() . '/' . $suffix);
        }
    }

    /**
     * Adds a row to data indicating even/odd row number
     *
     * @return object $rows
     */
    public function getRows($rowname = "rows")
    {
        if (!isset($this->$rowname) || !is_array($this->$rowname) || !count($this->$rowname)) {
            return;
        }

        $k = 0;
        foreach($this->$rowname as $row) {
            $row->odd = $k;
            $k = 1 - $k;
        }

        return $this->$rowname;
    }

    /**
     * Add path for common templates.
     * Includes src/View/{View}/tmpl/ (J6 location) with highest priority.
     */
    protected function addCommonTemplatePath()
    {
        $viewTitle = ucfirst($this->getName());
        $template  = Factory::getApplication()->getTemplate();

        // J6 src/View/{View}/tmpl/ — checked first
        $srcTmpl = JPATH_SITE . '/components/com_planjeagenda/site/src/View/' . $viewTitle . '/tmpl';
        if (is_dir($srcTmpl)) {
            $this->addTemplatePath($srcTmpl);
            if (!empty($this->_layoutStyleSuffix) && is_dir($srcTmpl . '/' . $this->_layoutStyleSuffix)) {
                $this->addTemplatePath($srcTmpl . '/' . $this->_layoutStyleSuffix);
            }
        }

        // Legacy common views/tmpl
        $this->addTemplatePath(JPATH_SITE . '/components/com_planjeagenda/common/views/tmpl');
        $this->addTemplatePath(JPATH_THEMES . '/' . $template . '/html/com_planjeagenda/common');

        if (!empty($this->_layoutStyleSuffix)) {
            $this->addTemplatePath(JPATH_SITE . '/components/com_planjeagenda/common/views/tmpl/' . $this->_layoutStyleSuffix);
            $this->addTemplatePath(JPATH_THEMES . '/' . $template . '/html/com_planjeagenda/common/' . $this->_layoutStyleSuffix);
        }
    }

    /**
     * Prepares the document.
     */
    protected function prepareDocument()
    {
        $app   = Factory::getApplication();
        $menus = $app->getMenu();
        $menu  = $menus->getActive();
        $print = $app->input->getBool('print', false);

        if ($print) {
            PlanjeagendaHelper::loadCss('print');
            $this->document->setMetaData('robots', 'noindex, nofollow');
        }

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            // TODO
            $this->params->def('page_heading', Text::_('com_planjeagenda_DEFAULT_PAGE_TITLE_DAY'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }
        $this->document->setTitle($title);

        // TODO: Metadata
        $this->document->setMetadata('keywords', $this->params->get('page_title'));
    }
}
