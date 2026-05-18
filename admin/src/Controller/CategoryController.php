<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

class CategoryController extends FormController
{
    protected $default_view = 'category';
    protected $text_prefix = 'COM_PLANJEAGENDA_CATEGORY';

    /**
     * Override cancel() to safely handle checkin without crashing if model unavailable.
     */
    public function cancel($key = null)
    {
        $this->checkToken();
        $app   = \Joomla\CMS\Factory::getApplication();
        $id    = $app->input->getInt('id', 0);

        // Try to checkin if we have an id
        if ($id) {
            try {
                $model = $this->getModel('Category', '', ['ignore_request' => true]);
                if ($model && method_exists($model, 'checkin')) {
                    $model->checkin($id);
                }
            } catch (\Throwable $e) {
                // Silently skip checkin if model fails
            }
        }

        $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_planjeagenda&view=categories', false));
        return true;
    }


    /*
     * Save
     */
    public function save($key = null, $urlVar = null)
    {
        return parent::save($key, $urlVar);
    }

    
    /*
     * GetModel
     */
    public function getModel($name = 'Category', $prefix = '', $config = [])
    {
        return parent::getModel($name ?: 'Category', $prefix, $config);
    }

}
