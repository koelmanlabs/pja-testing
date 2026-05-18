<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Logs;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    
    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        
        $this->addToolbar();
        parent::display($tpl);
    }
    
    
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_LOGS_TITLE'), 'list');
        
        // Knop om logs te wissen (deze gaat wel via de LogsController)
        ToolbarHelper::deleteList(Text::_('COM_PLANJEAGENDA_CONFIRM_CLEAR_LOGS'), 'logs.clear', 'JSEARCH_FILTER_CLEAR');
        
        // De "Cancel" knop als directe link naar de Main view
        $bar = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
        $bar->appendButton('Link', 'cancel', 'JTOOLBAR_CLOSE', 'index.php?option=com_planjeagenda&view=main');
    }
    
}
