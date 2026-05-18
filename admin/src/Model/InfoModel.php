<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;

class InfoModel extends BaseDatabaseModel
{
    public function getSystemInfo()
    {
        $db = $this->getDatabase();
        $app = Factory::getApplication();
        
        return [
            'php_version'    => PHP_VERSION,
            'db_version'     => $db->getVersion(),
            'db_type'        => $db->name,
            'memory_limit'   => ini_get('memory_limit'),
            'upload_max'     => ini_get('upload_max_filesize'),
            'joomla_version' => JVERSION,
            'server_info'    => $_SERVER['SERVER_SOFTWARE']
        ];
    }
    
    public function getExtensionInfo()
    {
        // Zoek informatie over de component zelf in de #__extensions tabel
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
        ->select($db->quoteName(['element', 'params', 'manifest_cache']))
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('element') . ' = ' . $db->quote('com_planjeagenda'));
        
        $db->setQuery($query);
        $result = $db->loadObject();
        
        return $result ? json_decode($result->manifest_cache) : null;
    }
    
    
    
    /**
     * Controleert of de vereiste tabellen en kolommen aanwezig zijn
     * @return array
     */
    public function getDatabaseStatus()
    {
        $db = $this->getDatabase();
        $tables = [
            '#__pja_events'     => ['id', 'title', 'locid', 'catid', 'published'],
            '#__pja_venues'     => ['id', 'title', 'street', 'published'],
            '#__pja_categories' => ['id', 'title', 'alias', 'published']
        ];
        
        $status = [];
        
        foreach ($tables as $table => $columns) {
            $exists = false;
            $missingColumns = [];
            
            try {
                // Controleer of tabel bestaat
                $db->setQuery("SHOW TABLES LIKE " . $db->quote($db->replacePrefix($table)));
                if ($db->loadResult()) {
                    $exists = true;
                    
                    // Controleer kolommen
                    $dbColumns = $db->getTableColumns($table);
                    foreach ($columns as $col) {
                        if (!array_key_exists($col, $dbColumns)) {
                            $missingColumns[] = $col;
                        }
                    }
                }
            } catch (\Exception $e) {
                $exists = false;
            }
            
            $status[$table] = [
                'exists'  => $exists,
                'missing' => $missingColumns,
                'healthy' => ($exists && empty($missingColumns))
            ];
        }
        
        return $status;
    }
}
