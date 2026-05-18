<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class CssmanagerController extends AdminController
{

    /**
     * Constructor
     */
/**
     * Proxy for getModel.
     */
    public function getModel($name = 'Cssmanager', $prefix = '', $config = array())
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

    /**
     *
     */
    public function cancel()
    {
        $this->setRedirect('index.php?option=com_planjeagenda&view=main');
    }

    public function back()
    {
        $this->setRedirect('index.php?option=com_planjeagenda&view=main');
    }
    /**
     *
     */
    public function linenumber()
    {
        $task  = Factory::getApplication()->input->get('task', '');
        $model = $this->getModel();

        switch ($task)
        {
            case 'setlinenumber' :
                $model->setStatusLinenumber(1);
                break;

            default :
                $model->setStatusLinenumber(0);
                break;
        }

        $this->setRedirect('index.php?option=com_planjeagenda&view=cssmanager');
    }

}
