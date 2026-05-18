<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Venue;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Session\Session;

/**
 * View class: Venue
 */
class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;
    
    
    protected $defaultAccess;
    protected $access;

    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        
        $model = $this->getModel();
        if (method_exists($model, 'getAttachments')) {
            // We halen de bijlagen op voor het huidige item ID
            $this->attachments = $model->getAttachments($this->item->id);
        }

        // Data binding
        if (!empty($this->form) && !empty($this->item)) {
            $this->form->bind($this->item);
        }

        $params = ComponentHelper::getParams('com_planjeagenda');
        
        if ($this->item->id == 0) {
            if (empty($this->item->city)) {
                $this->item->city = $params->get('default_city', '');
            }
            if (empty($this->item->country)) {
                $this->item->country = $params->get('default_country', 'Nederland');
            }
        }

//        $this->defaultAccess = $params->get('default_attachment_access', 1);
//        $this->access = HTMLHelper::_('access.assetgroups');

        $wa = $this->document->getWebAssetManager();
        
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $wa = $this->document->getWebAssetManager();
        
        // 1. Dropzone Assets (Lichtgewicht & stabiel)
        $wa->registerAndUseStyle('dropzone-css', 'https://unpkg.com/dropzone@5/dist/min/dropzone.min.css');
        $wa->registerAndUseScript('dropzone-js', 'https://unpkg.com/dropzone@5/dist/min/dropzone.min.js', [], ['defer' => true]);
        
        // 2. Eigen script voor de logica
        $wa->registerAndUseScript(
            'com_planjeagenda.venue',
            'com_planjeagenda/venue.js',
            ['dependencies' => ['jquery', 'core', 'dropzone-js']],
            ['defer' => true]
            );
        
        // CSRF Token doorgeven
        $this->document->addScriptOptions('csrf.token', \Joomla\CMS\Session\Session::getFormToken());
        
        $wa->useScript('keepalive')
        ->useScript('form.validate'); // Dit script voorkomt 'document.formvalidator is undefined'
        
        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        $app   = Factory::getApplication();
        $isNew = ($this->item->id == 0);
        $canDo = ContentHelper::getActions('com_planjeagenda', 'venue', $this->item->id);

        $app->input->set('hidemainmenu', true);

        ToolbarHelper::title(
            $isNew ? Text::_('COM_PLANJEAGENDA_ADD_VENUE') : Text::_('COM_PLANJEAGENDA_EDIT_VENUE'), 
            'location venue'
        );

        if ($canDo->get('core.edit') || ($isNew && $canDo->get('core.create'))) {
            ToolbarHelper::apply('venue.apply');
            ToolbarHelper::save('venue.save');
        }

        ToolbarHelper::cancel('venue.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_planjeagenda');
        }
    }
}