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
use Joomla\CMS\Language\Text;

class SampledataController extends BaseController
{
    /**
     * Constructor
     */
/**
     * Process sampledata
     */
    public function load()
    {
        $model = $this->getModel('Sampledata');

        if (!$model->loadData()) {
            $msg = Text::_('com_planjeagenda_SAMPLEDATA_FAILED');
        } else {
            $msg = Text::_('com_planjeagenda_SAMPLEDATA_SUCCESSFULL');
        }

        $link = 'index.php?option=com_planjeagenda&view=main';

        $this->setRedirect($link, $msg);
     }
}
