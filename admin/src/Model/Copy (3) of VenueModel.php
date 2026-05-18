<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaDebug;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\AttachmentHelper;
use Joomla\CMS\Filesystem\File;   // Cruciaal voor File::copy en File::makeSafe
use Joomla\CMS\Filesystem\Folder; // Cruciaal voor Folder::exists en Folder::create
use Joomla\CMS\Filter\OutputFilter;

/**
 * Model: Venue
 */
class VenueModel extends AdminModel
{
    protected $text_prefix = 'COM_PLANJEAGENDA_VENUE';
    
    protected $context = 'com_planjeagenda.venue';

    /**
     * Zorg dat het model weet welk ID geladen moet worden uit de URL
     */
    protected function populateState()
    {
        $app = Factory::getApplication();
        $pk  = $app->input->getInt('id');
        $this->setState('venue.id', $pk);

        parent::populateState();
    }

    public function getTable($type = 'Venue', $prefix = 'Administrator', $config = array())
    {
        return Factory::getApplication()->bootComponent('com_planjeagenda')
            ->getMVCFactory()->createTable($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_planjeagenda.venue', 'venue', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Dit vult de velden in je formulier
     */
    protected function loadData()
    {
        // 1. Probeer data uit de sessie (bijv. na een typefout van de gebruiker)
        $data = Factory::getApplication()->getUserState($this->context . '.data', array());
        
        // 2. Als de sessie leeg is, pak de data van het item (uit de database)
        if (empty($data)) {
            $data = $this->getItem();
            
            // Als het een object is, zet het om naar een array/gegevens voor het formulier
            if (is_object($data)) {
                $data = (array) $data;
            }
        }
        
        return $data;
    }

    public function save($data)
    {
        // Log de binnenkomende data voor debugging
        // debug log removed
        
        // Voorkom overschrijven: Als ID 0 is, zorg dat Joomla het als nieuw ziet
        if (isset($data['id']) && (int) $data['id'] === 0) {
            unset($data['id']);
        }
        
        // 1. Voer de standaard Joomla opslag uit
        $result = parent::save($data);
        
        if ($result) {
            // Pak het ID van de opgeslagen venue
            $id  = (int) $this->getState($this->getName() . '.id');
            $app = \Joomla\CMS\Factory::getApplication();
            
            // 2. Pak de data uit het jform object (POST)
            $jformData   = $app->input->get('jform', [], 'array');
            $base64Files = $jformData['attachments_data'] ?? [];
            $fileNames   = $jformData['attachments_names'] ?? [];
            
            if (!empty($base64Files)) {
                foreach ($base64Files as $key => $base64String) {
                    if (empty($base64String)) continue;
                    
                    // 3. Haal de pure base64 content eruit
                    $parts = explode(',', $base64String);
                    if (count($parts) < 2) continue;
                    
                    $dataContent = base64_decode($parts[1]);
                    
                    // 4. Bepaal MIME-type en Extensie
                    $finfo    = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($dataContent);
                    
                    // Haal de extensie uit de originele naam (indien aanwezig)
                    // of bepaal deze op basis van het MIME-type
                    $originalName = $fileNames[$key] ?? 'upload';
                    $extension    = pathinfo($originalName, PATHINFO_EXTENSION);
                    
                    // Mocht de extensie leeg zijn (bijv. door de JS verwerking), bepaal hem dan op basis van mime
                    if (empty($extension)) {
                        $mimeMap = [
                            'image/jpeg'      => 'jpg',
                            'image/png'       => 'png',
                            'image/gif'       => 'gif',
                            'application/pdf' => 'pdf',
                            'application/msword' => 'doc',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
                        ];
                        $extension = $mimeMap[$mimeType] ?? 'bin'; // 'bin' als fallback
                    }
                    
                    // --- SANITIZING ---
                    $cleanBaseName = \Joomla\CMS\Filter\OutputFilter::stringURLSafe(pathinfo($originalName, PATHINFO_FILENAME));
                    $safeFileName  = $cleanBaseName . '.' . $extension;
                    
                    // 5. Maak tijdelijk bestand aan in /tmp met de dynamische extensie
                    $tmpFilePath = JPATH_SITE . '/tmp/' . $safeFileName;
                    
                    if (file_put_contents($tmpFilePath, $dataContent) !== false) {
                        // 6. Bereid de array voor.
                        // 'name' is hier de schone naam voor het bestandssysteem.
                        $singleFile = [
                            'name'         => $safeFileName,
                            'type'         => $mimeType,
                            'tmp_name'     => $tmpFilePath,
                            'error'        => 0,
                            'size'         => strlen($dataContent),
                            'display_name' => $userInputName // Optioneel voor gebruik in de helper
                        ];
                        
                        // 7. Helper verwerkt het bestand en de database
                        $uploaded = AttachmentHelper::postUpload($singleFile, 'venue', $id);
                        
                        if (!$uploaded) {
                            $app->enqueueMessage('Bijlage ' . $userInputName . ' kon niet worden verwerkt.', 'warning');
                        }
                        
                        // 8. Opruimen
                        if (file_exists($tmpFilePath)) {
                            @unlink($tmpFilePath);
                        }
                    }
                }
            }
        // debug log removed
        }
        
        return $result;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        
        if ($item && !empty($item->id)) {
            $item->attachments = $this->getAttachments($item->id);
        } else {
            if ($item) $item->attachments = [];
        }
        
        return $item;
    }

    public function getAttachments($id)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*')
        ->from($db->quoteName('#__pja_attachments'))
        ->where($db->quoteName('object') . ' = ' . $db->quote('venue'))
        ->where($db->quoteName('object_id') . ' = ' . (int) $id)
        ->order('ordering ASC');
        
        $db->setQuery($query);
        
        return $db->loadObjectList();
    }

    public function delete(&$pks = array())
    {
        $db = $this->getDatabase();
        
        foreach ($pks as $i => $pk) {
            $query = $db->getQuery(true)
                ->select('COUNT(id)')
                ->from($db->quoteName('#__pja_events'))
                ->where($db->quoteName('locid') . ' = ' . (int) $pk);
            
            $db->setQuery($query);
            
            if ($db->loadResult() > 0) {
                $this->setError(Text::_('COM_PLANJEAGENDA_VENUE_ASSIGNED_EVENT'));
                unset($pks[$i]);
                continue;
            }
            
            // Opschonen bijlagen
            $queryAttach = $db->getQuery(true)
                ->select('id')
                ->from($db->quoteName('#__pja_attachments'))
                ->where($db->quoteName('object_id') . ' = ' . (int) $pk)
                ->where($db->quoteName('object') . ' = ' . $db->quote('venue'));
            
            $db->setQuery($queryAttach);
            $attachments = $db->loadColumn();
            
            if (!empty($attachments)) {
                foreach ($attachments as $attachId) {
                    AttachmentHelper::remove($attachId);
                }
            }
        }
        
        return parent::delete($pks);
    }
    
    
    public function removeAttachment($id)
    {
        if (empty($id)) {
            return false;
        }
        
        // Roep de helper aan die het zware werk doet
        return AttachmentHelper::remove($id);
    }
    
    
    /**
     * Verwerk de bijlagen uit de Dropzone conform de specifieke tabelstructuur
     */
    public function processAttachmentsXXX($venueId, $data)
    {
        $attachmentsData  = $data['attachments_data'] ?? [];
        $attachmentsNames = $data['attachments_names'] ?? [];
        
        if (empty($attachmentsData)) {
            return true;
        }
        
        $db   = $this->getDbo();
        $user = \Joomla\CMS\Factory::getUser();
        $now  = \Joomla\CMS\Factory::getDate()->toSql();
        $path = JPATH_SITE . '/images/planjeagenda/attachments/';
        
        // Zorg dat de map bestaat
        if (!\Joomla\CMS\Filesystem\Folder::exists($path)) {
            \Joomla\CMS\Filesystem\Folder::create($path);
        }
        
        foreach ($attachmentsData as $index => $base64Data) {
            if (empty($base64Data)) continue;
            
            // 1. Namen voorbereiden
            $userInputName = $attachmentsNames[$index] ?? 'bijlage';
            $extension     = 'jpg';
            
            // Maak een veilige bestandsnaam voor op de schijf
            $cleanBaseName = \Joomla\CMS\Filter\OutputFilter::stringURLSafe(pathinfo($userInputName, PATHINFO_FILENAME));
            $finalFileName = $cleanBaseName . '.' . $extension;
            
            // 2. Dubbele bestandsnamen op de server voorkomen
            $fullPath = $path . $finalFileName;
            $counter  = 1;
            while (\Joomla\CMS\Filesystem\File::exists($fullPath)) {
                $finalFileName = $cleanBaseName . '_' . $counter . '.' . $extension;
                $fullPath      = $path . $finalFileName;
                $counter++;
            }
            
            // 3. Bestand decoderen en wegschrijven
            $base64String = str_replace(' ', '+', explode(',', $base64Data)[1]);
            $fileContent  = base64_decode($base64String);
            $fileSize     = strlen($fileContent); // Grootte in bytes
            
            if (\Joomla\CMS\Filesystem\File::write($fullPath, $fileContent)) {
                
                // 4. Query opbouwen conform jouw tabelstructuur
                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__pja_attachments'))
                ->columns([
                    $db->quoteName('object'),      // Bijv. 'venue'
                    $db->quoteName('object_id'),   // Het ID van de locatie
                    $db->quoteName('file'),        // De veilige bestandsnaam
                    $db->quoteName('name'),        // De gebruiksvriendelijke naam
                    $db->quoteName('filename'),    // De volledige bestandsnaam
                    $db->quoteName('path'),        // Relatieve pad voor web
                    $db->quoteName('filetype'),    // MIME-type
                    $db->quoteName('filesize'),    // Grootte
                    $db->quoteName('added'),       // Datum toegevoegd
                    $db->quoteName('added_by'),    // Door wie
                    $db->quoteName('created'),     // Datum aanmaak
                    $db->quoteName('frontend'),    // Zichtbaarheid
                    $db->quoteName('access')       // Toegangsniveau
                ])
                ->values(
                    $db->quote('venue') . ', ' .
                    (int) $venueId . ', ' .
                    $db->quote($finalFileName) . ', ' .
                    $db->quote($userInputName) . ', ' . // De 'mooie' naam uit het tekstveld
                    $db->quote($finalFileName) . ', ' .
                    $db->quote('images/planjeagenda/attachments/' . $finalFileName) . ', ' .
                    $db->quote('image/jpeg') . ', ' .
                    (int) $fileSize . ', ' .
                    $db->quote($now) . ', ' .
                    (int) $user->id . ', ' .
                    $db->quote($now) . ', ' .
                    '1, 1' // Standaard frontend=1 en access=1
                    );
                
                $db->setQuery($query);
                $db->execute();
            }
        }
        
        return true;
    }
    
    
}