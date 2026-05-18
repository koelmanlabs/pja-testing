<?php
namespace KoelmanLabs\Component\Planjeagenda\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use KoelmanLabs\Component\Planjeagenda\Site\Service\IcsCalendarService;
use Laminas\Diactoros\Response;

class EventslistController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }
}
