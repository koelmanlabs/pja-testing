<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class PlanjeagendaViewImport extends PlanjeagendaAdminView
{
    public function display($tpl = null)
    {
        ToolbarHelper::title(Text::_('com_planjeagenda_IMPORT_DATA'), 'upload');
        ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_planjeagenda');
        parent::display($tpl);
    }
}
