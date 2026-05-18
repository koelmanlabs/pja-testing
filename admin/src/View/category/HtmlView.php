<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Category;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class HtmlView extends BaseHtmlView
{
    protected $form; protected $item; protected $state; protected $canDo; protected $Lists;

    public function display($tpl = null)
    {
        $app         = Factory::getApplication();
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        $this->canDo = ContentHelper::getActions('com_planjeagenda');
        if (count($errors = (array)($this->get('Errors') ?? []))) {
            $app->enqueueMessage(implode("\n", $errors), 'error'); return false;
        }
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('planjeagenda.backend');
        $wa->registerStyle('planjeagenda.colorpicker', 'com_planjeagenda/colorpicker.css')->useStyle('planjeagenda.colorpicker');
        $wa->registerScript('planjeagenda.colorpicker_js', 'com_planjeagenda/colorpicker.js')->useScript('planjeagenda.colorpicker_js');

        $groups    = $this->get('Groups');
        $grouplist = [];
        if (!empty($this->item->groupid) && !array_key_exists($this->item->groupid, $groups)) {
            $grouplist[] = HTMLHelper::_('select.option', $this->item->groupid,
                Text::sprintf('COM_PLANJEAGENDA_CATEGORY_UNKNOWN_GROUP', $this->item->groupid));
        }
        $grouplist[] = HTMLHelper::_('select.option', '0', Text::_('COM_PLANJEAGENDA_CATEGORY_NO_GROUP'));
        $grouplist   = array_merge($grouplist, $groups);
        $this->Lists['groups'] = HTMLHelper::_('select.genericlist', $grouplist, 'groupid',
            ['size'=>'1','class'=>'inputbox form-select m-0'], 'value', 'text', $this->item->groupid);

        $app->input->set('hidemainmenu', true);
        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        $user       = Factory::getApplication()->getIdentity();
        $isNew      = ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
        $canDo      = $this->canDo;

        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_CATEGORY_BASE_'.($isNew?'ADD':'EDIT').'_TITLE'),
            'category-'.($isNew?'add':'edit'));

        if ($isNew && count($user->getAuthorisedCategories('com_planjeagenda', 'core.create')) > 0) {
            ToolbarHelper::apply('category.apply');
            ToolbarHelper::save('category.save');
            ToolbarHelper::save2new('category.save2new');
        } elseif (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_user_id == $user->id))) {
            ToolbarHelper::apply('category.apply');
            ToolbarHelper::save('category.save');
            if ($canDo->get('core.create')) { ToolbarHelper::save2new('category.save2new'); }
        }
        if (!$isNew && $canDo->get('core.create')) { ToolbarHelper::save2copy('category.save2copy'); }
        ToolbarHelper::cancel('category.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        ToolbarHelper::divider();
        ToolbarHelper::inlinehelp();
    }
}
