<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

if (!class_exists('PlanjeagendaImage')) {
    require_once JPATH_SITE . '/components/com_planjeagenda/classes/image.class.php';
}

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\Path;
use Joomla\CMS\Router\Route;

class ImagehandlerController extends BaseController
{
    /**
     * Constructor
     */
/**
     * logic for uploading an image
     *
     * @access public
     * @return void
     */
    public function uploadimage()
    {
        // Check for request forgeries
        Session::checkToken() or die('Invalid token');

        $app = Factory::getApplication();
        $jemsettings = \PlanjeagendaHelper::config();

        $file = $app->input->files->get('userfile', [], 'array');
        $task = $app->input->get('task', '');


        //set the target directory
        if ($task == 'venueimgup') {
            $base_Dir = JPATH_SITE.'/images/klevents/venues/';
        } else if ($task == 'eventimgup') {
            $base_Dir = JPATH_SITE.'/images/klevents/events/';
        } else if ($task == 'categoriesimgup') {
            $base_Dir = JPATH_SITE.'/images/klevents/categories/';
        }

        //do we have an upload?
        if (empty($file['name'])) {
            echo "<script> alert('".Text::_('com_planjeagenda_IMAGE_EMPTY')."'); window.history.go(-1); </script>\n";
            $app->close();
        }

        //check the image
        $check = \PlanjeagendaImage::check($file, $jemsettings);

        if ($check === false) {
            $app->redirect(\Joomla\CMS\Router\Route::_('index.php?option=com_planjeagenda&view=imagehandler', false));
        }

        //sanitize the image filename
        $filename = \PlanjeagendaImage::sanitize($base_Dir, $file['name']);
        $filepath = $base_Dir . $filename;

        //upload the image
        if (!File::upload($file['tmp_name'], $filepath)) {
            echo "<script> alert('".Text::_('com_planjeagenda_UPLOAD_FAILED')."'); </script>\n";
            $app->close();
        } else {
            echo "<script> alert('".Text::_('com_planjeagenda_UPLOAD_COMPLETE')."'); window.parent.SelectImage('$filename', '$filename'); </script>\n";
            $app->close();
        }
    }

    /**
     * logic to mass delete images
     *
     * @access public
     * @return void
     */
    public function delete()
    {
        // Check for request forgeries
        Session::checkToken('get') or die('Invalid Token');

        $app = Factory::getApplication();


        // Get some data from the request
        $images = $app->input->get('rm', array(), 'array');
        $folder = $app->input->get('folder', '');

        if (count($images)) {
            foreach ($images as $image) {
                if ($image !== InputFilter::getInstance()->clean($image, 'path')) {
                    Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_UNABLE_TO_DELETE').' '.htmlspecialchars($image, ENT_COMPAT, 'UTF-8'), 'warning');
                    continue;
                }

                $fullPath = Path::clean(JPATH_SITE.'/images/klevents/'.$folder.'/'.$image);
                $fullPaththumb = Path::clean(JPATH_SITE.'/images/klevents/'.$folder.'/small/'.$image);
                if (is_file($fullPath)) {
                    File::delete($fullPath);
                    if (File::exists($fullPaththumb)) {
                        File::delete($fullPaththumb);
                    }
                }
            }
        }

        if ($folder == 'events') {
            $task = 'selecteventimg';
        } else if ($folder == 'venues') {
            $task = 'selectvenueimg';
        } else if ($folder == 'categories') {
            $task = 'selectcategoriesimg';
        }

        $app->redirect('index.php?option=com_planjeagenda&view=imagehandler&task='.$task.'&tmpl=component');
    }

}
