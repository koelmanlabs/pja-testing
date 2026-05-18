<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\AttachmentHelper;

/**
 * Event Controller
 */
class EventController extends FormController
{
    protected $default_view = 'event';

    protected $text_prefix = 'COM_PLANJEAGENDA_EVENT';

    /**
     * Hook called after a successful save.
     */
    protected function postSaveHook($model, $validData = [])
    {
        $id    = $model->getState($this->context . '.id');
        $isNew = $model->getState($this->context . '.new');

        PluginHelper::importPlugin('planjeagenda');
        $app = Factory::getApplication();
        $app->triggerEvent('onEventEdited', [$id, $isNew]);

        // Use direct DB check — PluginHelper::isEnabled() can return stale cached results
        // when the plugin group is not auto-loaded by Joomla core on boot.
        if (!self::isPlanjeagendaMailerEnabled()) {
            $app->enqueueMessage(Text::_('COM_PLANJEAGENDA_GLOBAL_MAILERPLUGIN_DISABLED'), 'notice');
        }
    }

    /**
     * AJAX: upload attachment (FilePond)
     */
    public function upload()
    {
        $this->checkToken('post');
        $app  = Factory::getApplication();
        $file = $app->input->files->get('file');
        $id   = $app->input->getInt('id');
        $res  = ['success' => false];

        if (!empty($file['name']) && $id > 0) {
            $uploaded = AttachmentHelper::postUpload($file, 'event', $id);
            if ($uploaded) {
                $res['success']  = true;
                $res['fileName'] = $file['name'];
                $res['message']  = Text::_('COM_PLANJEAGENDA_UPLOAD_SUCCESS');
            } else {
                $res['message'] = Text::_('COM_PLANJEAGENDA_UPLOAD_FAILED');
            }
        }

        header('Content-Type: application/json');
        echo json_encode($res);
        $app->close();
    }

    /**
     * AJAX: remove attachment
     */
    public function ajaxRemoveAttachment()
    {
        while (ob_get_level()) { ob_end_clean(); }
        $this->checkToken('post');
        $id  = $this->input->getInt('id');
        $res = ['success' => false];

        if ($id && AttachmentHelper::remove($id)) {
            $res['success'] = true;
        }

        header('Content-Type: application/json');
        echo json_encode($res);
        Factory::getApplication()->close();
    }

    /**
     * Direct DB check for the mailer plugin — bypasses PluginHelper static cache.
     */
    protected static function isPlanjeagendaMailerEnabled(): bool
    {
        try {
            $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery(
                $db->getQuery(true)
                    ->select('enabled')
                    ->from('#__extensions')
                    ->where('type = '    . $db->quote('plugin'))
                    ->where('folder = '  . $db->quote('planjeagenda'))
                    ->where('element = ' . $db->quote('planjeagenda_mailer'))
            );
            return (bool) $db->loadResult();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
