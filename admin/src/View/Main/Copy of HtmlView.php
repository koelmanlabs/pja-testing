<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Main;
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaFactory;
use stdClass; // Nodig voor de 'new stdClass()' aanroepen
use KoelmanLabs\Component\Planjeagenda\Administrator\Model\UpdatecheckModel;

/**
 * View class for the JEM home screen
 *
 * @package JEM
 */
class HtmlView extends BaseHtmlView
{

    public function display($tpl = null)
    {
        // Load pane behavior
        // jimport() removed: Joomla 6 uses PSR-4 autoloading. Add 'use' statement instead.

        //initialise variables
        $app      = Factory::getApplication();
        $document = $app->getDocument();
        $user     = Factory::getApplication()->getIdentity();

        // Get main model data
        $events   = $this->get('EventsData')   ?: new stdClass();
        $venue    = $this->get('VenuesData')   ?: new stdClass();
        $category = $this->get('CategoriesData') ?: new stdClass();

        $updateModel = new UpdatecheckModel(['ignore_request' => true]);
        $updatedata  = $updateModel->getUpdatedata();

        if ($updatedata === false) {
            $updatedata = new stdClass();
            $updatedata->failed  = 1;
            $updatedata->current = null;
        }

        // Load CSS
        $wa = $document->getWebAssetManager();
        if (!$wa->assetExists('style', 'planjeagenda.backend')) {
           $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css');
        }
        $wa->useStyle('planjeagenda.backend');

        // Assign variables to template
        $this->events     = $events;
        $this->venue      = $venue;
        $this->category   = $category;
        $this->user       = $user;
        $this->updatedata = $updatedata;

        // Add toolbar
        $this->addToolbar();

        // Render template
        parent::display($tpl);
    }

    /**
     * Add Toolbar
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('com_planjeagenda_MAIN_TITLE'), 'home');

        if (Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_planjeagenda')) {
            ToolbarHelper::preferences('com_planjeagenda');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('listevents', true, 'https://www.koelmanlabs.nl/documentation/manual/backend/control-panel');
    }

    /**
     * Creates the buttons view
     *
     * @param  string  $link  targeturl
     * @param  string  $image path to image
     * @param  string  $text  image description
     * @param  boolean $modal 1 for loading in modal
     */
    protected function quickiconButton($link, $image, $text, $modal = 0)
    {
        // Initialise variables
        $lang = Factory::getApplication()->getLanguage();
        ?>
        <div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
            <div class="icon">
                <?php if ($modal == 1) : ?>
                    <a href="<?php echo $link.'&amp;tmpl=component'; ?>" style="cursor:pointer" class="modal"
                            rel="{handler: 'iframe', size: {x: 650, y: 400}}">
                        <?php echo HTMLHelper::_('image', 'com_planjeagenda/'.$image, $text, NULL, true); ?>
                        <span><?php echo $text; ?></span>
                    </a>
                <?php else : ?>
                    <a href="<?php echo $link; ?>">
                        <?php echo HTMLHelper::_('image', 'com_planjeagenda/'.$image, $text, NULL, true); ?>
                        <span><?php echo $text; ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>
