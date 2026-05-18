<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Attendee;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class HtmlView extends BaseHtmlView
{{
    protected $lists; protected $row;

    public function display($tpl = null)
    {{
        $app = Factory::getApplication();
        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');

        $id = $app->input->getInt('id', 0);
        $this->event = $app->input->getInt('eventid', 0);
        $row = $this->get('Data');
        $this->lists['users'] = HTMLHelper::_('list.users', 'uid', $row->uid ?? 0, false, null, 'name', 0);
        $this->row = $row;
        $this->addToolbar();
        return parent::display($tpl);
    }}

    protected function addToolbar()
    {{
        $app  = Factory::getApplication();
        $app->input->set('hidemainmenu', true);
        $cid   = $app->input->get('cid', [], 'array');
        $canDo = ContentHelper::getActions('com_planjeagenda');
        ToolbarHelper::title(
            empty($cid[0]) ? Text::_('COM_PLANJEAGENDA_ADD_ATTENDEE') : Text::_('COM_PLANJEAGENDA_EDIT_ATTENDEE'),
            'users'
        );
        if ($canDo->get('core.edit') || $canDo->get('core.create')) {{
            ToolbarHelper::apply('attendee.apply');
            ToolbarHelper::save('attendee.save');
        }}
        if ($canDo->get('core.create')) {{ ToolbarHelper::save2new('attendee.save2new'); }}
        if (!empty($cid[0]) && $canDo->get('core.create')) {{ ToolbarHelper::save2copy('attendee.save2copy'); }}
        ToolbarHelper::cancel('attendee.cancel', empty($cid[0]) ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }}
}}
