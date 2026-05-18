<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Group;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Filter\OutputFilter;

class HtmlView extends BaseHtmlView
{
    protected $form; protected $item; protected $state; protected $lists;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        if (count($errors = (array)($this->get('Errors') ?? []))) {
            $app->enqueueMessage(implode("\n", $errors), 'error'); return false;
        }
        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');

        $maintainers     = $this->get('Members');
        $availableUsers  = $this->get('Available');
        OutputFilter::objectHTMLSafe($this->item);

        $this->lists['maintainers']     = HTMLHelper::_('select.genericlist', $maintainers, 'maintainers[]',
            ['class'=>'inputbox','size'=>'20','multiple'=>'multiple','style'=>'width:98%'], 'value', 'text');
        $this->lists['available_users'] = HTMLHelper::_('select.genericlist', $availableUsers, 'available_users',
            ['class'=>'inputbox','size'=>'20','multiple'=>'multiple','style'=>'width:98%'], 'value', 'text');

        $this->task = $app->input->get('task', '');
        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        $app        = Factory::getApplication();
        $app->input->set('hidemainmenu', true);
        $user       = $app->getIdentity();
        $isNew      = ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
        $canDo      = ContentHelper::getActions('com_planjeagenda');
        ToolbarHelper::title($isNew ? Text::_('COM_PLANJEAGENDA_GROUP_ADD') : Text::_('COM_PLANJEAGENDA_GROUP_EDIT'), 'groupedit');
        if (!$checkedOut && ($canDo->get('core.edit') || $canDo->get('core.create'))) {
            ToolbarHelper::apply('group.apply');
            ToolbarHelper::save('group.save');
        }
        if (!$checkedOut && $canDo->get('core.create')) { ToolbarHelper::save2new('group.save2new'); }
        if (!$isNew && $canDo->get('core.create'))      { ToolbarHelper::save2copy('group.save2copy'); }
        ToolbarHelper::cancel('group.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        ToolbarHelper::divider();
        ToolbarHelper::inlinehelp();
    }
}
