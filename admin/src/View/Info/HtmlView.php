<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Info;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
    protected $sysInfo;
    protected $extInfo;
    
    public function display($tpl = null)
    {
        $this->sysInfo = $this->get('SystemInfo');
        $this->extInfo = $this->get('ExtensionInfo');
        $this->dbStatus = $this->get('DatabaseStatus');
        
        $this->addToolbar();
        parent::display($tpl);
    }
    
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_INFO'), 'info');
        ToolbarHelper::back('COM_PLANJEAGENDA_BACK_TO_DASHBOARD', 'index.php?option=com_planjeagenda');
    }
}
