<?php
/**
 * @package     Planjeagenda
 * @copyright   (C) 2026 Koelman Labs
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
// Gebruik de directe Joomla Framework namespaces in plaats van de CMS-aliassen
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/**
 * Helper voor het beheren van bijlagen
 */
class AttachmentHelper
{
    /**
     * Verwerkt de upload van een bestand
     */
    public static function postUpload($file, $object, $id)
    {
        // Forceer laden filesystem
        // Filesystem classes are autoloaded via PSR-4
        
        if (empty($file['tmp_name']) || empty($id)) {
            return false;
        }
        
        $fileName  = File::makeSafe($file['name']);
        $cleanName = str_replace(' ', '_', $fileName);
        $relativeFolder = 'media/com_planjeagenda/attachments/' . $object . '/' . $id;
        $absolutePath   = JPATH_SITE . '/' . $relativeFolder;
        
        if (!Folder::exists($absolutePath)) {
            Folder::create($absolutePath);
        }
        
        if (File::exists($absolutePath . '/' . $cleanName)) {
            $cleanName = date('His') . '_' . $cleanName;
        }
        
        $destination = $absolutePath . '/' . $cleanName;
        $success     = false;
        
        /**
         * VERBETERDE UPLOAD LOGICA
         * We proberen eerst de officiële Joomla File::upload.
         * Als dat faalt (bijv. door stream-wrappers van FilePond), vallen we terug op copy.
         */
        if (is_uploaded_file($file['tmp_name'])) {
            $success = File::upload($file['tmp_name'], $destination);
        }
        
        // Fallback: Als File::upload niet lukte, maar het tijdelijke bestand bestaat wel
        if (!$success && file_exists($file['tmp_name'])) {
            if (@copy($file['tmp_name'], $destination)) {
                $success = true;
                @chmod($destination, 0644);
            }
        }
        
        if ($success) {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $now   = Factory::getDate()->toSql();
            
            // Let op: 'filetype' vullen we met de MIME-type van de upload
            $columns = [
                'object', 'object_id', 'file', 'name', 'filename',
                'path', 'filetype', 'filesize', 'created', 'added',
                'ordering', 'frontend'
            ];
            
            $values  = [
                $db->quote($object),
                (int) $id,
                $db->quote($cleanName),
                $db->quote($cleanName),
                $db->quote($cleanName),
                $db->quote($relativeFolder . '/' . $cleanName),
                $db->quote($file['type']), // Hier komt 'video/mp4' te staan
                (int) $file['size'],
                $db->quote($now),
                $db->quote($now),
                0, 1
            ];
            
            $query->insert($db->quoteName('#__pja_attachments'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
            
            $db->setQuery($query);
            
            try {
                return $db->execute();
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return false;
    }
    
    

    public static function remove($id)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__pja_attachments'))
            ->where($db->quoteName('id') . ' = ' . (int) $id);
        
        $db->setQuery($query);
        $attachment = $db->loadObject();
        
        if (!$attachment) return false;
        
        $filePath = JPATH_SITE . '/' . $attachment->path;
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
        
        $deleteQuery = $db->getQuery(true)
            ->delete($db->quoteName('#__pja_attachments'))
            ->where($db->quoteName('id') . ' = ' . (int) $id);
        
        $db->setQuery($deleteQuery);
        return $db->execute();
    }
}