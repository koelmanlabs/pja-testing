<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class CategoriesController extends AdminController
{

    protected $text_prefix = 'COM_PLANJEAGENDA_CATEGORIES';


    /**
     * Proxy for getModel
     *
     * @param    string    $name    The model name. Optional.
     * @param    string    $prefix    The class prefix. Optional.
     *
     * @return    object    The model.
     */
    public function getModel($name = 'Category', $prefix = '', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Rebuild the nested set tree.
     *
     * @return    bool    False on failure or error, true on success.
     */
    public function rebuild()
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $this->setRedirect(Route::_('index.php?option=com_planjeagenda&view=categories', false));

        // Initialise variables.
        $model = $this->getModel();

        if ($model->rebuild()) {
            // Rebuild succeeded.
            $this->setMessage(Text::_('com_planjeagenda_CATEGORIES_REBUILD_SUCCESS'));
            return true;
        } else {
            // Rebuild failed.
            $this->setMessage(Text::_('com_planjeagenda_CATEGORIES_REBUILD_FAILURE'));
            return false;
        }
    }


     /**
      * Logic to delete categories
      *
      * @access public
      * @return void
      *
      */
     public function remove()
     {
        // Check for request forgeries
        Session::checkToken() or die('Invalid Token');

         $cid= Factory::getApplication()->input->post->get('cid', array(), 'array');

         if (!is_array($cid) || count($cid) < 1) {
             Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_SELECT_ITEM_TO_DELETE'), 'warning');
         }

         $model = $this->getModel('Category');

         $msg = $model->delete($cid);

         $cache = Factory::getCache('com_planjeagenda');
         $cache->clean();

         $this->setRedirect('index.php?option=com_planjeagenda&view=categories', $msg);
     }

}
