<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */


namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\AttachmentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaDebug;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelperBackend;

/**
 * JEM Component Controller
 */
class DisplayController extends BaseController
{
    /**
     * De namespace prefix voor de views.
     * Dit vertelt Joomla: "Kijk in de View map, niet in de Controller map."
     */
    protected $view_namespace = 'KoelmanLabs\\Component\\Planjeagenda\\Administrator\\View';
    
    /**
     * @var    string The default view.
     */
    protected $default_view = 'main';


    /**
     * Display the view
     */
    public function display($cachable = false, $urlparams = [])
    {
        // debug log removed
        // Load the submenu - but not on edit views.
        // if no view found then refert to main
        $jinput = Factory::getApplication()->input;
        $view = $jinput->getCmd('view', 'main');
        // add all views you won't see the submenu / sidebar
        //  - on J! 2.5 param 'hidemainmenu' let's not show the submenu
        //    but on J! 3.x the submenu (sidebar) is shown with non-clickable entries.
        //    The alternative would be to move the addSubmenu call to all views the sidebar should be shown.
        static $views_without_submenu = array('attendee', 'category', 'event', 'group', 'source', 'venue');

        if (!in_array($view, $views_without_submenu)) {
            PlanjeagendaHelperBackend::addSubmenu($view);
        }
        
        return parent::display($cachable, $urlparams);
    }

    /**
     * Delete attachment
     *
     * Views: event, venue
     * Reference to the task is located in the attachments.js
     *
     * @return true on sucess
     * @access public
     */
    public function ajaxattachremove()
    {
        // Check for request forgeries
        Session::checkToken('request') or die('Invalid Token');

        $id = Factory::getApplication()->input->getInt('id', 0);

        $res = AttachmentHelper::remove($id);
        if (!$res) {
            echo 0;
            die();
        }

        $cache = Factory::getCache('com_planjeagenda');
        $cache->clean();

        echo 1;
        die();
    }
}
