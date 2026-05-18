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
use Joomla\CMS\Router\Route;

/**
 * 
 */
class VenueController extends FormController
{
    /**
     * De prefix voor taalbestanden (bijv. COM_PLANJEAGENDA_VENUE_SAVE_SUCCESS)
     */
    protected $text_prefix = 'COM_PLANJEAGENDA_VENUE';

    /**
     * Na het opslaan van een locatie
     */
    protected function postSaveHook($model, $validData = array())
    {
        $id    = $model->getState($this->context . '.id');
        $isNew = $model->getState($this->context . '.new');

        // Importeer plugins
        PluginHelper::importPlugin('planjeagenda');
        
        // In Joomla 6 gebruiken we de native Dispatcher via de Application
        $app = Factory::getApplication();
        $app->triggerEvent('onVenueEdited', array($id, $isNew));

        // Waarschuwing als de mailer uit staat
        if (!self::isPlanjeagendaMailerEnabled()) {
            $app->enqueueMessage(Text::_('COM_PLANJEAGENDA_GLOBAL_MAILERPLUGIN_DISABLED'), 'notice');
        }
    }
    
    
    public function saveXXX($key = null, $urlVar = null)
    {
        // Check voor security token
        $this->checkToken();
        
        $app   = \Joomla\CMS\Factory::getApplication();
        $model = $this->getModel('Venue');
        $data  = $app->input->post->get('jform', [], 'array');
        
        // We laten de standaard Joomla save zijn werk doen voor de hoofdvelden
        $result = parent::save($key, $urlVar);
        
        if ($result) {
            // Als de locatie is opgeslagen, verwerken we de bijlagen via het model
            // We halen het ID op van het item dat net is opgeslagen (nieuw of bestaand)
            $id = $app->input->getInt('id') ?: $app->input->getInt($urlVar);
            
            if ($id) {
                $model->processAttachments($id, $data);
            }
        }
        
        return $result;
    }
    
    
    public function ajaxRemoveAttachment()
    {
        // 1. Schoon de output buffer om eventuele eerdere echoes/waarschuwingen te wissen
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $this->checkToken('post');
        
        $id  = $this->input->getInt('id');
        $res = ['success' => false];
        
        if ($id) {
            // Gebruik de static call naar de helper
            if (AttachmentHelper::remove($id)) {
                $res['success'] = true;
            } else {
                $res['message'] = 'Helper kon bestand niet verwijderen';
            }
        }
        
        // 2. Zet de juiste header
        header('Content-Type: application/json');
        
        // 3. Output en stop onmiddellijk
        echo json_encode($res);
        
        // Gebruik de Joomla manier om de applicatie netjes te stoppen zonder extra witregels
        Factory::getApplication()->close();
    }
    
    
    
    public function removeAttachment()
    {
        $this->checkToken('get');
        
        $app    = \Joomla\CMS\Factory::getApplication();
        $id     = $app->input->getInt('id'); // Venue ID
        $attId  = $app->input->getInt('attachment_id'); // Attachment ID
        
        $model  = $this->getModel('Venue');
        
        if ($model->removeAttachment($attId)) {
            $this->setMessage(\Joomla\CMS\Language\Text::_('COM_PLANJEAGENDA_ATTACHMENT_REMOVED'));
        } else {
            $this->setMessage(\Joomla\CMS\Language\Text::_('COM_PLANJEAGENDA_ATTACHMENT_REMOVE_FAILED'), 'error');
        }
        
        // De Controller regelt de navigatie
        $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_planjeagenda&view=venue&layout=edit&id=' . $id, false));
    }
    
}