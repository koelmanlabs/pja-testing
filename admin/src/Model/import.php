<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;

class ImportModel extends BaseDatabaseModel
{
    /** @var array Resultaat van laatste import */
    public array $importResult = [];

    /**
     * Parseer een geüpload CSV bestand.
     * Geeft de eerste 5 rijen terug als preview + de kolomnamen.
     *
     * @param  string  $filePath   Pad naar het CSV bestand
     * @param  string  $separator  Scheidingsteken
     * @param  string  $delimiter  Tekstveld delimiter
     * @return array   ['headers' => [], 'preview' => [], 'total' => int]
     */
    public function parseCsvPreview(string $filePath, string $separator = ';', string $delimiter = '"'): array
    {
        if (!file_exists($filePath)) {
            return ['error' => 'Bestand niet gevonden: ' . $filePath];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['error' => 'Kan bestand niet openen.'];
        }

        // Skip BOM indien aanwezig
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = [];
        $preview = [];
        $total   = 0;

        $row = fgetcsv($handle, 0, $separator, $delimiter);
        if ($row !== false) {
            $headers = array_map('trim', $row);
        }

        while (($row = fgetcsv($handle, 0, $separator, $delimiter)) !== false) {
            $total++;
            if (count($preview) < 5) {
                $preview[] = array_combine(
                    $headers,
                    array_pad($row, count($headers), '')
                );
            }
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'preview' => $preview,
            'total'   => $total,
        ];
    }

    /**
     * Valideer CSV rijen voor import.
     * Geeft een array van fouten per rij terug.
     *
     * @param  string  $filePath   Pad naar CSV
     * @param  array   $mapping    Kolomkoppeling: ['csv_kolom' => 'db_veld']
     * @param  string  $type       events|venues|categories
     * @param  string  $separator
     * @param  string  $delimiter
     * @return array   ['valid' => int, 'errors' => [['row'=>N, 'message'=>'...']]]
     */
    public function validateCsv(string $filePath, array $mapping, string $type, string $separator = ';', string $delimiter = '"'): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['valid' => 0, 'errors' => [['row' => 0, 'message' => 'Kan bestand niet openen.']]];
        }

        // Skip BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Lees headers
        $headers = fgetcsv($handle, 0, $separator, $delimiter);

        $errors     = [];
        $valid      = 0;
        $rowNum     = 1;
        $maxErrors  = 50;

        $requiredFields = $this->getRequiredFields($type);

        while (($row = fgetcsv($handle, 0, $separator, $delimiter)) !== false) {
            $rowNum++;

            if (count($errors) >= $maxErrors) {
                $errors[] = ['row' => 0, 'message' => 'Meer dan ' . $maxErrors . ' fouten gevonden. Import gestopt.'];
                break;
            }

            // Koppel CSV kolommen aan velden
            $mapped = [];
            foreach ($mapping as $csvCol => $dbField) {
                if (empty($dbField)) {
                    continue;
                }
                $idx = array_search($csvCol, $headers);
                if ($idx !== false && isset($row[$idx])) {
                    $mapped[$dbField] = trim($row[$idx]);
                }
            }

            // Valideer verplichte velden
            $rowErrors = [];
            foreach ($requiredFields as $field => $label) {
                if (empty($mapped[$field])) {
                    $rowErrors[] = "Veld '{$label}' is verplicht maar leeg.";
                }
            }

            // Type-specifieke validatie
            if ($type === 'events' && !empty($mapped['dates'])) {
                if (!$this->isValidDate($mapped['dates'])) {
                    $rowErrors[] = "Ongeldige datum: '{$mapped['dates']}'. Gebruik formaat YYYY-MM-DD.";
                }
            }

            if (!empty($rowErrors)) {
                $errors[] = ['row' => $rowNum, 'message' => implode(' | ', $rowErrors)];
            } else {
                $valid++;
            }
        }

        fclose($handle);

        return ['valid' => $valid, 'errors' => $errors];
    }

    /**
     * Importeer CSV bestand naar de database.
     *
     * @param  string  $filePath
     * @param  array   $mapping
     * @param  string  $type
     * @param  bool    $overwrite   Bestaande records overschrijven?
     * @param  string  $separator
     * @param  string  $delimiter
     * @return array   ['inserted'=>N, 'updated'=>N, 'skipped'=>N, 'errors'=>[]]
     */
    public function importCsv(
        string $filePath,
        array  $mapping,
        string $type,
        bool   $overwrite  = false,
        string $separator  = ';',
        string $delimiter  = '"'
    ): array {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['inserted'=>0,'updated'=>0,'skipped'=>0,'errors'=>['Kan bestand niet openen.']];
        }

        // Skip BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers  = fgetcsv($handle, 0, $separator, $delimiter);
        $result   = ['inserted'=>0,'updated'=>0,'skipped'=>0,'errors'=>[]];
        $db       = Factory::getContainer()->get('DatabaseDriver');
        $table    = $this->getTableName($type);
        $rowNum   = 1;

        while (($row = fgetcsv($handle, 0, $separator, $delimiter)) !== false) {
            $rowNum++;

            // Koppel kolommen
            $data = [];
            foreach ($mapping as $csvCol => $dbField) {
                if (empty($dbField)) {
                    continue;
                }
                $idx = array_search($csvCol, $headers);
                if ($idx !== false && isset($row[$idx])) {
                    $data[$dbField] = trim($row[$idx]);
                }
            }

            if (empty($data)) {
                $result['skipped']++;
                continue;
            }

            try {
                // Controleer op duplicaat
                $existing = $this->findExisting($data, $type, $db);

                if ($existing && !$overwrite) {
                    $result['skipped']++;
                    continue;
                }

                if ($existing && $overwrite) {
                    // Update
                    $data['id'] = $existing;
                    $obj = (object)$data;
                    $db->updateObject('#__pja_' . $table, $obj, 'id');
                    $result['updated']++;
                } else {
                    // Insert
                    unset($data['id']);
                    // Voeg verplichte velden toe met defaults
                    $data = $this->addDefaults($data, $type);
                    $obj  = (object)$data;
                    $db->insertObject('#__pja_' . $table, $obj);
                    $result['inserted']++;
                }

            } catch (\Throwable $e) {
                $result['errors'][] = "Rij {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);
        return $result;
    }

    /**
     * Importeer een JSON backup bestand.
     *
     * @param  string  $filePath
     * @param  bool    $overwrite
     * @return array
     */
    public function importJson(string $filePath, bool $overwrite = false): array
    {
        $content = file_get_contents($filePath);
        if (!$content) {
            return ['error' => 'Kan bestand niet lezen.'];
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Ongeldig JSON bestand: ' . json_last_error_msg()];
        }

        // Controleer of het een Plan Je Agenda export is
        if (empty($data['export_info']['component']) || $data['export_info']['component'] !== 'com_planjeagenda') {
            return ['error' => 'Dit JSON bestand is niet afkomstig van Plan Je Agenda.'];
        }

        $result = ['inserted'=>0,'updated'=>0,'skipped'=>0,'errors'=>[]];
        $db     = Factory::getContainer()->get('DatabaseDriver');

        // Importeer in volgorde: categorieën → locaties → evenementen → relaties
        foreach (['categories','venues','events'] as $type) {
            if (empty($data[$type])) {
                continue;
            }

            $table = $this->getTableName($type);

            foreach ($data[$type] as $row) {
                $row = (array)$row;
                try {
                    $existing = $this->findExisting($row, $type, $db);

                    if ($existing && !$overwrite) {
                        $result['skipped']++;
                        continue;
                    }

                    if ($existing && $overwrite) {
                        $row['id'] = $existing;
                        $db->updateObject('#__pja_' . $table, (object)$row, 'id');
                        $result['updated']++;
                    } else {
                        unset($row['id']);
                        $db->insertObject('#__pja_' . $table, (object)$row);
                        $result['inserted']++;
                    }
                } catch (\Throwable $e) {
                    $result['errors'][] = ucfirst($type) . ': ' . $e->getMessage();
                }
            }
        }

        return $result;
    }

    /**
     * Geeft de veldnamen terug die beschikbaar zijn voor koppeling.
     */
    public function getAvailableFields(string $type): array
    {
        $fields = [
            'events' => [
                'title'            => 'Titel',
                'alias'            => 'Alias (URL)',
                'dates'            => 'Startdatum (YYYY-MM-DD)',
                'enddates'         => 'Einddatum (YYYY-MM-DD)',
                'times'            => 'Starttijd (HH:MM)',
                'endtimes'         => 'Eindtijd (HH:MM)',
                'description'      => 'Beschrijving',
                'introtext'        => 'Introductie tekst',
                'published'        => 'Gepubliceerd (0/1)',
                'featured'         => 'Uitgelicht (0/1)',
                'registra'         => 'Registratie (0/1)',
                'maxplaces'        => 'Max deelnemers',
                'waitinglist'      => 'Wachtlijst (0/1)',
                'recurrence_type'  => 'Herhaling type',
                'recurrence_number'=> 'Herhaling aantal',
            ],
            'venues' => [
                'venue'            => 'Naam locatie',
                'alias'            => 'Alias (URL)',
                'street'           => 'Straat',
                'number'           => 'Huisnummer',
                'postalcode'       => 'Postcode',
                'city'             => 'Stad',
                'state'            => 'Provincie',
                'country'          => 'Land',
                'locdescription'   => 'Beschrijving',
                'published'        => 'Gepubliceerd (0/1)',
                'latitude'         => 'Breedtegraad',
                'longitude'        => 'Lengtegraad',
            ],
            'categories' => [
                'catname'          => 'Naam categorie',
                'alias'            => 'Alias (URL)',
                'description'      => 'Beschrijving',
                'published'        => 'Gepubliceerd (0/1)',
                'color'            => 'Kleur (#RRGGBB)',
            ],
        ];

        return $fields[$type] ?? [];
    }

    // =========================================================================
    // Hulpfuncties
    // =========================================================================

    private function getTableName(string $type): string
    {
        return match($type) {
            'events'     => 'events',
            'venues'     => 'venues',
            'categories' => 'categories',
            default      => $type,
        };
    }

    private function getRequiredFields(string $type): array
    {
        return match($type) {
            'events'     => ['title' => 'Titel', 'dates' => 'Startdatum'],
            'venues'     => ['venue' => 'Naam locatie'],
            'categories' => ['catname' => 'Naam categorie'],
            default      => [],
        };
    }

    private function findExisting(array $data, string $type, $db): ?int
    {
        $table = '#__pja_' . $this->getTableName($type);

        // Zoek op alias als die er is
        if (!empty($data['alias'])) {
            $query = $db->getQuery(true)
                ->select('id')
                ->from($table)
                ->where('alias = ' . $db->quote($data['alias']));
            $id = (int)$db->setQuery($query)->loadResult();
            if ($id) return $id;
        }

        // Zoek op titel/naam
        $titleField = match($type) {
            'events'     => 'title',
            'venues'     => 'venue',
            'categories' => 'catname',
            default      => null,
        };

        if ($titleField && !empty($data[$titleField])) {
            $query = $db->getQuery(true)
                ->select('id')
                ->from($table)
                ->where($db->quoteName($titleField) . ' = ' . $db->quote($data[$titleField]));
            $id = (int)$db->setQuery($query)->loadResult();
            if ($id) return $id;
        }

        return null;
    }

    private function addDefaults(array $data, string $type): array
    {
        $data['created'] = $data['created'] ?? date('Y-m-d H:i:s');

        if ($type === 'events') {
            $data['published']  = $data['published']  ?? 1;
            $data['registra']   = $data['registra']   ?? 0;
            $data['featured']   = $data['featured']   ?? 0;
            $data['maxplaces']  = $data['maxplaces']  ?? 0;
            $data['waitinglist']= $data['waitinglist'] ?? 0;
            if (empty($data['alias']) && !empty($data['title'])) {
                $data['alias'] = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($data['title']);
            }
        }

        if ($type === 'venues') {
            $data['published'] = $data['published'] ?? 1;
            if (empty($data['alias']) && !empty($data['venue'])) {
                $data['alias'] = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($data['venue']);
            }
        }

        if ($type === 'categories') {
            $data['published']  = $data['published'] ?? 1;
            $data['parent_id']  = $data['parent_id'] ?? 1;
            $data['level']      = $data['level']     ?? 1;
            $data['access']     = $data['access']    ?? 1;
            if (empty($data['alias']) && !empty($data['catname'])) {
                $data['alias'] = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($data['catname']);
            }
        }

        return $data;
    }

    private function isValidDate(string $date): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                new \DateTime($date);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }
}
