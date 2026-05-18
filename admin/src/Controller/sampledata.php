<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

/**
 * JEM Component Sampledata Controller
 * @package JEM
 */
class SampledataController extends BaseController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Process sampledata
     */
    public function load()
    {
        $model = $this->getModel('sampledata');

        if (!$model->loadData()) {
            $msg = Text::_('com_planjeagenda_SAMPLEDATA_FAILED');
        } else {
            $msg = Text::_('com_planjeagenda_SAMPLEDATA_SUCCESSFULL');
        }

        $link = 'index.php?option=com_planjeagenda&view=main';

        $this->setRedirect($link, $msg);
     }
}
?>
