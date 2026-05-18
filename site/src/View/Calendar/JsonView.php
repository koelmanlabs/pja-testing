<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Calendar;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonView as BaseJsonView;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class JsonView extends BaseJsonView
{
    public function display($tpl = null)
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        
        // 1. FullCalendar datums opvangen en opschonen
        $startParam = substr($input->getString('start', ''), 0, 10);
        $endParam   = substr($input->getString('end', ''), 0, 10);
        $muni       = $input->getString('municipality', 'all');
        $cat        = $input->getString('catid', 'all');

        // 2. Model laden
        $mvcFactory = $app->bootComponent('com_planjeagenda')->getMVCFactory();
        $model = $mvcFactory->createModel('Calendar', 'Site');

        if ($model) {
            $model->setState('filter.municipality', $muni);
            $model->setState('filter.category', $cat);
            $rows = $model->getItems();
        } else {
            $rows = [];
        }

        $events = [];

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $color = !empty($row->cat_color) ? $row->cat_color : '#2e7d32';

                // Intro tekst opschonen voor de popup
                $intro = '';
                if (!empty($row->introtext)) {
                    $intro = strip_tags($row->introtext);
                    $intro = mb_substr($intro, 0, 150);
                    $intro = str_replace(["\r", "\n", '"', '\\'], ' ', $intro);
                }

                $venueText = !empty($row->venue) ? trim($row->venue . ($row->city ? ', ' . $row->city : '')) : '';

                // Basis event object voor FullCalendar
                $eventData = [
                    'id'              => (int) $row->id,
                    'title'           => htmlspecialchars_decode($row->title, ENT_QUOTES),
                    'backgroundColor' => $color,
                    'borderColor'     => $color,
                    'textColor'       => '#ffffff',
                    'url'             => Route::_('index.php?option=com_planjeagenda&view=event&id=' . $row->id),
                    'extendedProps'   => [
                        'catid'    => (int) $row->catid,
                        'category' => $row->catname ?? '',
                        'venue'    => $venueText,
                        'intro'    => $intro,
                    ],
                ];

                // 3. WATERDICHTE RECURRENCE / HERHALING CHECK
                // We controleren zowel de JEM 'recurrence' kolom als de logica dat 'enddates' verder ligt dan 'dates'
                $hasRecurrenceRule = isset($row->recurrence) && (int)$row->recurrence > 0;
                $hasMultiDayRange  = (!empty($row->enddates) && $row->enddates !== '0000-00-00' && $row->enddates !== $row->dates);

                // Als het een JEM herhaling is, óf als de einddatum verder weg ligt en er is herhalingslogica:
                if ($hasRecurrenceRule || ($hasMultiDayRange && isset($row->recurrence))) {
                    
                    // Haal de pure starttijd en eindtijd op (bijv. 19:00 en 21:00)
                    $startTime = !empty($row->times) ? substr($row->times, 0, 5) : '00:00';
                    $endTime   = !empty($row->endtimes) ? substr($row->endtimes, 0, 5) : '';

                    $eventData['startTime'] = $startTime;
                    if (!empty($endTime) && $endTime !== '00:00') {
                        $eventData['endTime'] = $endTime;
                    }

                    // Geef de datumgrenzen van de herhaling mee aan FullCalendar
                    $eventData['startRecur'] = $row->dates; 
                    if (!empty($row->enddates) && $row->enddates !== '0000-00-00') {
                        // Let op: FullCalendar stopt VÓÓR deze datum, dus we voegen voor de zekerheid de range toe
                        $eventData['endRecur'] = $row->enddates;
                    }

                    // Bepaal de dag van de week op basis van de startdatum (0 = zondag, 1 = maandag, enz.)
                    $dayOfWeek = (int)date('w', strtotime($row->dates));

                    // Vertaling van JEM herhalingstypes naar dagen van de week
                    $recType = isset($row->recurrence) ? (int)$row->recurrence : 2;
                    if ($recType == 1) {
                        // Dagelijks
                        $eventData['daysOfWeek'] = [0, 1, 2, 3, 4, 5, 6];
                    } else {
                        // Wekelijks (of fallback): Alleen op de specifieke weekdag tonen!
                        $eventData['daysOfWeek'] = [$dayOfWeek];
                    }

                    // CRUCIAL: Verwijder harde 'start' en 'end' datums zodat FullCalendar GEEN lange balken kan trekken!
                    unset($eventData['start']);
                    unset($eventData['end']);

                } else {
                    // 4. GEWONE ENKELVOUDIGE ACTIVITEIT (Géén herhaling)
                    $startFormat = $row->dates . (!empty($row->times) ? 'T' . $row->times : '');
                    
                    $eventData['start'] = $startFormat;
                    $eventData['allDay'] = (empty($row->times) || $row->times == '00:00:00');

                    // Alleen een einddatum meegeven als deze binnen dezelfde dag valt (bijv. met een eindtijd),
                    // anders trekt FullCalendar een lange balk.
                    if (!empty($row->endtimes) && $row->endtimes !== '00:00:00') {
                        $eventData['end'] = $row->dates . 'T' . $row->endtimes;
                    }
                }

                $events[] = $eventData;
            }
        }

        // Buffer opschonen en JSON uitsturen
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($events);
        $app->close();
    }
}