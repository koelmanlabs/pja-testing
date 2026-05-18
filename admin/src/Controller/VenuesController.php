<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Controller voor het overzicht van Venues.
 * AdminController regelt automatisch: publish, unpublish, trash, delete, etc.
 */
class VenuesController extends AdminController
{
    /**
     * De prefix voor de taalbestanden (bijv. COM_PLANJEAGENDA_VENUES_N_ITEMS_DELETED)
     */
    protected $text_prefix = 'COM_PLANJEAGENDA_VENUES';
    
    /**
     * Proxy voor getModel om het juiste model (Venue) te laden.
     */
    public function getModel($name = 'Venue', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}