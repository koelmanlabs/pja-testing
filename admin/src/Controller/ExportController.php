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
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

class ExportController extends BaseController
{
    public function getModel($name = 'Export', $prefix = '', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Exporteer evenementen als CSV.
     */
    public function exportEventsCsv(): void
    {
        Session::checkToken() or die('Invalid Token');

        $model   = $this->getModel();
        $options = $this->getOptions();
        $rows    = $model->getEvents($options);
        $config  = \PlanjeagendaHelper::config();
        
        // CHANGED
        $csv = $model->toCsv(
            $rows,
            $config->get('csv_separator', ';'),
            $config->get('csv_delimiter', '"'),
            (bool) $config->get('csv_bom', true)
        );

        $filename = 'planjeagenda_evenementen_' . date('Ymd-His') . '.csv';
        $this->sendDownload($csv, $filename, 'text/csv; charset=UTF-8');
    }

    /**
     * Exporteer locaties als CSV.
     */
    public function exportVenuesCsv(): void
    {
        Session::checkToken() or die('Invalid Token');

        $model = $this->getModel();
        $rows  = $model->getVenues($this->getOptions());
        $config = \PlanjeagendaHelper::config();

        $csv = $model->toCsv(
            $rows,
            $config->get('csv_separator', ';'),
            $config->get('csv_delimiter', '"'),
            (bool) $config->get('csv_bom', true)
        );

        $filename = 'planjeagenda_locaties_' . date('Ymd-His') . '.csv';
        $this->sendDownload($csv, $filename, 'text/csv; charset=UTF-8');
    }

    /**
     * Exporteer categorieën als CSV.
     */
    public function exportCategoriesCsv(): void
    {
        Session::checkToken() or die('Invalid Token');

        $model = $this->getModel();
        $rows  = $model->getCategories();
        $config = \PlanjeagendaHelper::config();

        $csv = $model->toCsv(
            $rows,
            $config->get('csv_separator', ';'),
            $config->get('csv_delimiter', '"'),
            (bool)$config->get('csv_bom', true)
        );

        $filename = 'planjeagenda_categorieen_' . date('Ymd-His') . '.csv';
        $this->sendDownload($csv, $filename, 'text/csv; charset=UTF-8');
    }

    /**
     * Volledig JSON backup export.
     */
    public function exportJson(): void
    {
        Session::checkToken() or die('Invalid Token');

        $model = $this->getModel();
        $json  = $model->toJson($this->getOptions());

        $filename = 'planjeagenda_backup_' . date('Ymd-His') . '.json';
        $this->sendDownload($json, $filename, 'application/json; charset=UTF-8');
    }

    /**
     * Exporteer als iCal (.ics).
     */
    public function exportIcal(): void
    {
        Session::checkToken() or die('Invalid Token');

        $model = $this->getModel();
        $ical  = $model->toIcal($this->getOptions());

        $filename = 'planjeagenda_' . date('Ymd-His') . '.ics';
        $this->sendDownload($ical, $filename, 'text/calendar; charset=UTF-8');
    }

    /**
     * Haal filter opties op uit het request.
     */
    private function getOptions(): array
    {
        $app = Factory::getApplication();

        return [
            'date_from' => $app->input->getString('date_from', ''),
            'date_to'   => $app->input->getString('date_to', ''),
            'published' => $app->input->getString('published', ''),
            'catid'     => $app->input->getInt('catid', 0),
        ];
    }

    /**
     * Stuur bestand als download naar de browser.
     */
    private function sendDownload(string $content, string $filename, string $contentType): void
    {
        // Schoon outputbuffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Sanitiseer bestandsnaam
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $content;
        exit;
    }
}
