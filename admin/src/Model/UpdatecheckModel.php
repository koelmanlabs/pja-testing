<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\Filesystem\File;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

class UpdatecheckModel extends BaseDatabaseModel
{
    
    public function getUpdatedata()
    {
        // We halen de versie op via de nieuwe universele helper methode
        $installedversion = \PlanjeagendaHelper::getExtensionVersion('com_planjeagenda', 'component');
        
        $updateFile = "https://www.koelmanlabs.nl/updatecheck/update_pkg_jem.xml";
        $checkFile  = self::CheckFile($updateFile);
        
        // Gebruik de backslash voor de globale PHP class stdClass
        $updatedata = new \stdClass();
        
        if ($checkFile) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'header'  => "User-Agent: Joomla-Update-Check\r\n"
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ]
            ]);
            $raw = @file_get_contents($updateFile, false, $ctx);
            
            if ($raw === false) {
                $updatedata->failed = 1;
                $updatedata->installedversion = $installedversion;
                return $updatedata;
            }
            
            $xml = simplexml_load_string($raw);
            $jversion = JVERSION;
            
            foreach($xml->update as $updatexml) {
                $targetVersion = (string)$updatexml->targetplatform["version"];
                
                // Check of deze update geschikt is voor de huidige Joomla versie
                if (preg_match('/^' . $targetVersion . '/', $jversion)) {
                    $updatedata->version          = (string)$updatexml->version;
                    $updatedata->versiondetail    = (string)$updatexml->version;
                    
                    // Datum omzetten naar Joomla formaat zonder externe Output class
                    $updatedata->date             = (new Date((string)$updatexml->date))->format('d-m-Y');
                    
                    $updatedata->info             = (string)$updatexml->infourl;
                    $updatedata->download         = (string)$updatexml->downloads->downloadurl;
                    $updatedata->notes            = explode(';', (string)$updatexml->notes);
                    $updatedata->changes          = explode(';', (string)$updatexml->changes);
                    $updatedata->failed           = 0;
                    $updatedata->installedversion = $installedversion;
                    $updatedata->current          = version_compare($installedversion, (string)$updatexml->version);
                }
            }
        } else {
            $updatedata->failed           = 1;
            $updatedata->installedversion = $installedversion;
        }
        
        return $updatedata;
    }
    
    protected static function CheckFile($filename)
    {
        $ext = File::getExt($filename);
        if ($ext === 'xml') {
            // Snelle check of bestand bereikbaar is
            $handle = @fopen($filename, 'r');
            if ($handle) {
                fclose($handle);
                return true;
            }
        }
        return false;
    }
}