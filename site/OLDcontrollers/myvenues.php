<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * JEM Component Myvenues Controller
 *
 * @package JEM
 *
 */
class PlanjeagendaControllerMyvenues extends BaseController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Logic to publish venues
     *
     * @access public
     * @return void
     */
    public function publish()
    {
        $this->setStatus(1, 'com_planjeagenda_VENUE_PUBLISHED');
    }

    /**
     * Logic unpublish venues
     */
    public function unpublish()
    {
        $this->setStatus(0, 'com_planjeagenda_VENUE_UNPUBLISHED');
    }

    /**
     * Logic to trash venues - NOT SUPPORTED YET
     *
     * @access public
     * @return void
     */
    /*
    public function trash()
    {
        $this->setStatus(-2, 'com_planjeagenda_VENUE_TRASHED');
    }
    */

    /**
     * Logic to publish/unpublish/trash venues
     *
     * @access protected
     * @return void
     */
    protected function setStatus($status, $message)
    {
        // Check for request forgeries
        Session::checkToken() or die('Invalid Token');

        $app = Factory::getApplication();
        $input = $app->input;

        $cid = $input->get('cid', array(), 'array');

        if (empty($cid)) {
            Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_SELECT_ITEM_TO_PUBLISH'), 'notice');
            $this->setRedirect(PlanjeagendaHelperRoute::getMyVenuesRoute());
            return;
        }

        $model = $this->getModel('myvenues');
        if (!$model->publish($cid, $status)) {
            echo "<script> alert('" . $model->getError() . "'); window.history.go(-1); </script>\n";
        }

        $total = count($cid);
        $msg   = $total . ' ' . Text::_($message);

        $this->setRedirect(PlanjeagendaHelperRoute::getMyVenuesRoute(), $msg);
    }
}
