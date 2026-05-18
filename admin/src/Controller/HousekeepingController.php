<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use KoelmanLabs\Component\Planjeagenda\Administrator\Model\HousekeepingModel;

class HousekeepingController extends BaseController
{
    /**
     * Constructor
     */
/**
     * logic to massdelete unassigned images
     *
     * @access public
     * @return void
     *
     */
    public function delete()
    {
        // Check for request forgeries
        Session::checkToken('get') or die('Invalid Token');

        $task = Factory::getApplication()->input->get('task', '');
        $model = $this->getModel('Housekeeping');
        if (!$model) {
            // Direct instantiation fallback
            $model = new \KoelmanLabs\Component\Planjeagenda\Administrator\Model\HousekeepingModel();
        }

        if ($task == 'cleaneventimg') {
            $total = $model->delete(HousekeepingModel::EVENTS);
        } elseif ($task == 'cleanvenueimg') {
            $total = $model->delete(HousekeepingModel::VENUES);
        } elseif ($task == 'cleancategoryimg') {
            $total = $model->delete(HousekeepingModel::CATEGORIES);
        }

        $link = 'index.php?option=com_planjeagenda&view=housekeeping';
        $msg = Text::sprintf('com_planjeagenda_HOUSEKEEPING_IMAGES_DELETED', $total);

        $this->setRedirect($link, $msg);
    }

    /**
     * logic to truncate table cats_relations
     *
     * @access public
     * @return void
     *
     */
    public function cleanupCatsEventRelations()
    {
        // Check for request forgeries
        Session::checkToken('get') or die('Invalid Token');

        $model = $this->getModel('Housekeeping');
        if (!$model) {
            // Direct instantiation fallback
            $model = new \KoelmanLabs\Component\Planjeagenda\Administrator\Model\HousekeepingModel();
        }
        $model->cleanupCatsEventRelations();

        $link = 'index.php?option=com_planjeagenda&view=housekeeping';
        $msg = Text::_('com_planjeagenda_HOUSEKEEPING_CLEANUP_CATSEVENT_RELS_DONE');

        $this->setRedirect($link, $msg);
    }

    /**
     * Truncates JEM tables with exception of settings table
     */
    public function truncateAllData()
    {
        // Check for request forgeries
        Session::checkToken('get') or die('Invalid Token');

        $model = $this->getModel('Housekeeping');
        if (!$model) {
            // Direct instantiation fallback
            $model = new \KoelmanLabs\Component\Planjeagenda\Administrator\Model\HousekeepingModel();
        }
        $model->truncateAllData();

        $link = 'index.php?option=com_planjeagenda&view=housekeeping';
        $msg = Text::_('com_planjeagenda_HOUSEKEEPING_TRUNCATE_ALL_DATA_DONE');

        $this->setRedirect($link, $msg);
    }

    /**
     * Triggerarchive + Recurrences
     *
     * @access public
     * @return void
     *
     */
    public function triggerarchive()
    {
        // Check for request forgeries
        Session::checkToken('get') or die('Invalid Token');

        \PlanjeagendaHelper::cleanup(1);

        $link = 'index.php?option=com_planjeagenda&view=housekeeping';
        $msg = Text::_('com_planjeagenda_HOUSEKEEPING_AUTOARCHIVE_DONE');

        $this->setRedirect($link, $msg);
    }
}
