<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaDebug;

class LogsController extends BaseController
{
    public function clear()
    {
        $this->checkToken();
        
        // Gebruik de clear methode van je debug helper (0 dagen = alles weg)
        PlanjeagendaDebug::clear(0);
        
        $this->setRedirect('index.php?option=com_planjeagenda&view=logs', 'Log tabel succesvol geleegd.');
    }
}
