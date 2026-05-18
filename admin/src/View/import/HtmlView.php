<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Import;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_IMPORT_DATA'), 'upload');
        ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_planjeagenda');
        return parent::display($tpl);
    }
}
