<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;

class ImportController extends BaseController
{
    /** @var string Tijdelijke upload map */
    private string $tmpDir;

        public function __construct($config = [], $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);
        $this->tmpDir = JPATH_ROOT . '/tmp/pja_import/';
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }
    }

    /**
     * Stap 1: Bestand uploaden en preview tonen.
     * Retourneert JSON voor de wizard.
     */
    public function upload(): void
    {
        Session::checkToken('request') or die('Invalid Token');

        $app   = Factory::getApplication();
        $input = $app->input;
        $type  = $input->getString('import_type', 'events');

        $file = $input->files->get('import_file', [], 'array');

        if (empty($file['name'])) {
            $this->sendJson(['error' => 'Geen bestand geüpload.']);
            return;
        }

        // Valideer extensie
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'json', 'txt'], true)) {
            $this->sendJson(['error' => 'Alleen CSV of JSON bestanden zijn toegestaan.']);
            return;
        }

        // Valideer bestandsgrootte (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            $this->sendJson(['error' => 'Bestand is te groot (max 10 MB).']);
            return;
        }

        // Sla op in tmp
        $tmpName = 'import_' . md5(uniqid()) . '.' . $ext;
        $tmpPath = $this->tmpDir . $tmpName;

        if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
            $this->sendJson(['error' => 'Kan bestand niet opslaan.']);
            return;
        }

        $model     = $this->getModel('Import', '');
        $separator = $input->getString('csv_separator', ';');
        $delimiter = $input->getString('csv_delimiter', '"');

        if ($ext === 'json') {
            // JSON: geen kolomkoppeling nodig, direct naar validatie
            $content = file_get_contents($tmpPath);
            $data    = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJson(['error' => 'Ongeldig JSON bestand.']);
                return;
            }

            if (empty($data['export_info']['component']) || $data['export_info']['component'] !== 'com_planjeagenda') {
                $this->sendJson(['error' => 'Dit bestand is geen Plan Je Agenda export.']);
                return;
            }

            $this->sendJson([
                'type'     => 'json',
                'tmp_file' => $tmpName,
                'summary'  => [
                    'evenementen' => count($data['events'] ?? []),
                    'locaties'    => count($data['venues'] ?? []),
                    'categorieën' => count($data['categories'] ?? []),
                    'relaties'    => count($data['relations'] ?? []),
                ],
                'export_info' => $data['export_info'],
            ]);

        } else {
            // CSV: parse en geef preview + kolomnamen
            $preview = $model->parseCsvPreview($tmpPath, $separator, $delimiter);

            if (isset($preview['error'])) {
                $this->sendJson(['error' => $preview['error']]);
                return;
            }

            $availableFields = $model->getAvailableFields($type);

            $this->sendJson([
                'type'             => 'csv',
                'tmp_file'         => $tmpName,
                'import_type'      => $type,
                'headers'          => $preview['headers'],
                'preview'          => $preview['preview'],
                'total'            => $preview['total'],
                'available_fields' => $availableFields,
                'separator'        => $separator,
                'delimiter'        => $delimiter,
            ]);
        }
    }

    /**
     * Stap 2: Valideer de kolomkoppeling.
     * Retourneert JSON met validatieresultaten.
     */
    public function validate(): void
    {
        Session::checkToken('request') or die('Invalid Token');

        $app       = Factory::getApplication();
        $input     = $app->input;
        $tmpFile   = basename($input->getString('tmp_file', ''));
        $type      = $input->getString('import_type', 'events');
        $mapping   = $input->get('mapping', [], 'array');
        $separator = $input->getString('csv_separator', ';');
        $delimiter = $input->getString('csv_delimiter', '"');

        if (empty($tmpFile) || !file_exists($this->tmpDir . $tmpFile)) {
            $this->sendJson(['error' => 'Tijdelijk bestand niet gevonden. Upload het bestand opnieuw.']);
            return;
        }

        $model  = $this->getModel('Import', '');
        $result = $model->validateCsv(
            $this->tmpDir . $tmpFile,
            $mapping,
            $type,
            $separator,
            $delimiter
        );

        $this->sendJson([
            'valid'  => $result['valid'],
            'errors' => $result['errors'],
        ]);
    }

    /**
     * Stap 3: Importeer de data.
     */
    public function doImport(): void
    {
        Session::checkToken('request') or die('Invalid Token');

        $app       = Factory::getApplication();
        $input     = $app->input;
        $tmpFile   = basename($input->getString('tmp_file', ''));
        $type      = $input->getString('import_type', 'events');
        $fileType  = $input->getString('file_type', 'csv');
        $mapping   = $input->get('mapping', [], 'array');
        $overwrite = (bool)$input->getInt('overwrite', 0);
        $separator = $input->getString('csv_separator', ';');
        $delimiter = $input->getString('csv_delimiter', '"');

        if (empty($tmpFile) || !file_exists($this->tmpDir . $tmpFile)) {
            $this->sendJson(['error' => 'Tijdelijk bestand niet gevonden.']);
            return;
        }

        $model  = $this->getModel('Import', '');
        $tmpPath = $this->tmpDir . $tmpFile;

        if ($fileType === 'json') {
            $result = $model->importJson($tmpPath, $overwrite);
        } else {
            $result = $model->importCsv($tmpPath, $mapping, $type, $overwrite, $separator, $delimiter);
        }

        // Verwijder tijdelijk bestand
        @unlink($tmpPath);

        $this->sendJson($result);
    }

    /**
     * Stuur JSON response.
     */
    private function sendJson(array $data): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function getModel($name = 'Import', $prefix = '', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
