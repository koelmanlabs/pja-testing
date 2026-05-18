<?php 

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Eventslist;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseView;
use KoelmanLabs\Component\Planjeagenda\Site\Service\IcsCalendarService;

class IcsView extends BaseView
{
    public function display($tpl = null)
    {
        $model = $this->getModel();
        $items = $model->getItems();
        
        $service = new IcsCalendarService();
        $ics = $service->build($items);
        
        // CLEAN OUTPUT ONLY (no layout system involved)
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="calendar.ics"');
        
        echo $ics;
        exit;
    }
}
