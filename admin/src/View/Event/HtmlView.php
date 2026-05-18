<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Event;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\ComponentHelper;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaConfig;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

// Voeg deze namespaces toe zodat de klassen in display() gevonden worden
// use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaAdmin;
// use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaCategories;

/**
 * Event View
 */
class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;
    protected $task;
    protected $Lists;
    protected $access;

    public function display($tpl = null)
    {
        // Initialise variables.
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        $this->params = ComponentHelper::getParams('com_planjeagenda');
        
        // VOEG DEZE CONTROLE TOE:
        if (!$this->form) {
            throw new \Exception('Form object is null in HtmlView');
        }

        // Check for errors.
        $errors = $this->get('Errors');
        if (is_array($errors) && count($errors)) {
            Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');
            return false;
        }

        // initialise variables
        $this->jemsettings = PlanjeagendaConfig::getInstance()->toObject();
        $app               = Factory::getApplication();
        $user              = Factory::getApplication()->getIdentity();
        // $this->settings    = \PlanjeagendaHelper::config();
        $this->settings = [];
        $this->task        = $app->input->get('task', '');
        
        // $categories     = \PlanjeagendaCategories::getCategoriesTree(1);
        // $selectedcats   = $this->get('Catsselected');

        // $this->Lists = array();
        // $this->Lists['category'] = \PlanjeagendaCategories::buildcatselect($categories, 'cid[]', $selectedcats, 0, 'multiple="multiple" size="8"');

        // Load assets via WebAssetManager
        $wa = $this->document->getWebAssetManager();
        $wa->useScript('jquery');
        
        // Eigen styles en scripts
        $wa->registerStyle('com_planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('com_planjeagenda.backend');
        
        // Let op: controleer of deze bestandsnamen exact kloppen met je /media/ map
        $wa->registerScript('com_planjeagenda.attachments', 'com_planjeagenda/attachment.js', [], ['relative' => true])->useScript('com_planjeagenda.attachments');
        $wa->registerScript('com_planjeagenda.recurrence', 'com_planjeagenda/recurrence.js', [], ['relative' => true])->useScript('com_planjeagenda.recurrence');
        $wa->registerScript('com_planjeagenda.unlimited', 'com_planjeagenda/unlimited.js', [], ['relative' => true])->useScript('com_planjeagenda.unlimited');
        $wa->registerScript('com_planjeagenda.seo', 'com_planjeagenda/seo.js', [], ['relative' => true])->useScript('com_planjeagenda.seo');

        // FilePond CSS (indien nog niet elders geladen)
        $wa->registerStyle('com_planjeagenda.attachment-css', 'com_planjeagenda/attachment.css', [], ['relative' => true])->useStyle('com_planjeagenda.attachment-css');

        $this->access = PlanjeagendaHelper::getAccesslevelOptions();

        if ($this->getLayout() == 'default') {
            $this->setLayout('edit');
        }
        
        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user  = Factory::getApplication()->getIdentity();
        $isNew      = ($this->item->id == 0);
        $canDo = ContentHelper::getActions('com_planjeagenda', 'event', $this->item->id);
        ToolbarHelper::title($isNew ? 'Planjeagenda: Nieuw Event' : 'Planjeagenda: Bewerk Event', 'calendar');
        
        if ($canDo->get('core.edit') || $canDo->get('core.create')) {
            ToolbarHelper::apply('event.apply');
            ToolbarHelper::save('event.save');
            if ($canDo->get('core.create')) {
                ToolbarHelper::save2new('event.save2new');
            }
            if (!empty($this->item->id) && $canDo->get('core.create')) {
                ToolbarHelper::save2copy('event.save2copy');
            }
        }

        ToolbarHelper::cancel('event.cancel', empty($this->item->id) ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        
        
        
        ToolbarHelper::help('editevents', true, 'https://www.koelmanlabs.nl/documentation/manual/backend/events/add-event');
    }
}