<?php
/**
 * @package     Joomla.Component
 * @subpackage  Com_Planjeagenda
 * @contact     KoelmanLabs
 * @copyright   Copyright (C) 2026 KoelmanLabs. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Eventslist;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\BaseHtmlView;
use Joomla\CMS\Factory;

/**
 * Raw View class for the Planjeagenda iCal export
 * 
 * @since  1.0.0
 */
class RawView extends HtmlView
{
    /**
     * The item records loaded from the model
     *
     * @var    array
     * @since  1.0.0
     */
    protected $items = [];

    /**
     * Display the iCal raw feed layout
     *
     * @param   string  $tpl  The name of the template file to parse
     * @return  void
     * @since   1.0.0
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $this->items = $this->get('Items') ?? [];

        // 1. Initialize the iCalcreator framework manager helper
        // Adapting to your custom vendor naming structure inside com_planjeagenda
        if (!class_exists('\\Kigkonsult\\Icalcreator\\Vcalendar')) {
            require_once JPATH_COMPONENT_SITE . '/src/Helper/icalcreator/autoload.php';
        }

        $config = [
            'unique_id' => 'klevents.koelmanlabs.nl',
            'TZID'      => 'UTC'
        ];

        $vcalendar = new \Kigkonsult\Icalcreator\Vcalendar($config);
        $vcalendar->setProperty('method', 'PUBLISH');
        $vcalendar->setProperty('x-wr-calname', 'Planjeagenda Events');
        $vcalendar->setProperty('x-wr-timezone', 'UTC');

        // 2. Loop through individual database records as standalone instances
        foreach ($this->items as $row) {
            
            // Extract and clean date strings from row object context
            $startDateStr = !empty($row->start_date) ? trim((string) $row->start_date) : null;
            $endDateStr   = !empty($row->end_date) ? trim((string) $row->end_date) : $startDateStr;
            
            if (!$startDateStr) {
                continue; // Skip invalid records safely
            }
            
            // Determine all-day layout flags
            $isAllDay = (bool) ($row->all_day ?? false);

            /**
             * CASE 1: ALL-DAY PROCESSING (SINGLE DAY & TRUE MULTI-DAY RANGES)
             */
            if ($isAllDay) {
                $dateParams = ['VALUE' => 'DATE'];
                
                try {
                    // Create isolated instances anchored to UTC to avoid server midnight shifts
                    $dtStartObj = new \DateTimeImmutable($startDateStr, new \DateTimeZone('UTC'));
                    $dtEndObj   = new \DateTimeImmutable($endDateStr, new \DateTimeZone('UTC'));
                    
                    // RFC 5545 Rule: All-day events require DTEND to be exclusive (+1 day).
                    // This covers single day (ends next morning) and multi-day ranges.
                    $dtEndObjExclusive = $dtEndObj->modify('+1 day');
                    
                    if ($dtEndObjExclusive <= $dtStartObj) {
                        $dtEndObjExclusive = $dtStartObj->modify('+1 day');
                    }
                    
                    $dtStartArray = [
                        'year'  => (int) $dtStartObj->format('Y'),
                        'month' => (int) $dtStartObj->format('m'),
                        'day'   => (int) $dtStartObj->format('d'),
                    ];
                    
                    $dtEndArray = [
                        'year'  => (int) $dtEndObjExclusive->format('Y'),
                        'month' => (int) $dtEndObjExclusive->format('m'),
                        'day'   => (int) $dtEndObjExclusive->format('d'),
                    ];
                    
                } catch (\Exception $e) {
                    // Resilient baseline fallback parsing strings manually if object breaks
                    $startYear  = (int) substr($startDateStr, 0, 4);
                    $startMonth = (int) substr($startDateStr, 5, 2);
                    $startDay   = (int) substr($startDateStr, 8, 2);
                    
                    $dtStartArray = ['year' => $startYear, 'month' => $startMonth, 'day' => $startDay];
                    $dtEndArray   = ['year' => $startYear, 'month' => $startMonth, 'day' => $startDay + 1];
                }

                // Append unique database ID mapping to stop background aggregator duplicates
                $uid = 'event-' . $row->id . '-' . ($row->alias ?? 'event') . '@planjeagenda';
                
                $vevent = $vcalendar->newComponent('vevent');
                $vevent->setProperty('uid', $uid);
                $vevent->setProperty('summary', $row->title ?? 'Untitled Event');
                $vevent->setProperty('description', strip_tags($row->introtext ?? ''));
                
                $vevent->setProperty('dtstart', $dtStartArray, $dateParams);
                $vevent->setProperty('dtend', $dtEndArray, $dateParams);
                
                if (!empty($row->venue)) {
                    $location = $row->venue . (!empty($row->city) ? ', ' . $row->city : '');
                    $vevent->setProperty('location', $location);
                }
                
                continue; // Standalone processing complete, leap straight to the next row

            } else {
                /**
                 * CASE 2: TIMED EVENTS (PROCESSED AS UNIQUE COPIES)
                 */
                $startTimeStr = !empty($row->start_time) ? trim((string) $row->start_time) : '00:00:00';
                $endTimeStr   = !empty($row->end_time) ? trim((string) $row->end_time) : '23:59:59';
                
                // Sanity check: Ensure sequence layout holds valid ranges
                if (strtotime($endDateStr . ' ' . $endTimeStr) <= strtotime($startDateStr . ' ' . $startTimeStr)) {
                    try {
                        $dtEndObj   = new \DateTimeImmutable($startDateStr . ' ' . $startTimeStr);
                        $dtEndObj   = $dtEndObj->modify('+1 hour');
                        $endDateStr = $dtEndObj->format('Y-m-d');
                        $endTimeStr = $dtEndObj->format('H:i:s');
                    } catch (\Exception $e) {
                        $endDateStr = $startDateStr;
                        $endTimeStr = '23:59:59';
                    }
                }

                try {
                    $dtStartObj = new \DateTimeImmutable($startDateStr . ' ' . $startTimeStr, new \DateTimeZone('UTC'));
                    $dtEndObj   = new \DateTimeImmutable($endDateStr . ' ' . $endTimeStr, new \DateTimeZone('UTC'));
                    
                    $dtStartArray = [
                        'year'  => (int) $dtStartObj->format('Y'),
                        'month' => (int) $dtStartObj->format('m'),
                        'day'   => (int) $dtStartObj->format('d'),
                        'hour'  => (int) $dtStartObj->format('H'),
                        'min'   => (int) $dtStartObj->format('i'),
                        'sec'   => (int) $dtStartObj->format('s'),
                    ];
                    
                    $dtEndArray = [
                        'year'  => (int) $dtEndObj->format('Y'),
                        'month' => (int) $dtEndObj->format('m'),
                        'day'   => (int) $dtEndObj->format('d'),
                        'hour'  => (int) $dtEndObj->format('H'),
                        'min'   => (int) $dtEndObj->format('i'),
                        'sec'   => (int) $dtEndObj->format('s'),
                    ];
                } catch (\Exception $e) {
                    continue; // Skip individual record if timestamps break
                }

                $uid = 'event-' . $row->id . '-' . ($row->alias ?? 'event') . '@planjeagenda';
                
                $vevent = $vcalendar->newComponent('vevent');
                $vevent->setProperty('uid', $uid);
                $vevent->setProperty('summary', $row->title ?? 'Untitled Event');
                $vevent->setProperty('description', strip_tags($row->introtext ?? ''));
                
                $vevent->setProperty('dtstart', $dtStartArray);
                $vevent->setProperty('dtend', $dtEndArray);
                
                if (!empty($row->venue)) {
                    $location = $row->venue . (!empty($row->city) ? ', ' . $row->city : '');
                    $vevent->setProperty('location', $location);
                }
            }
        }

        // 3. Render headers and clear buffer for clean raw text output stream
        $app->mimeType = 'text/calendar';
        $app->setHeader('Content-Type', 'text/calendar; charset=utf-8', true);
        $app->setHeader('Content-Disposition', 'inline; filename="planjeagenda.ics"', true);
        $app->sendHeaders();

        // Output raw data and terminate cleanly
        echo $vcalendar->createCalendar();
        $app->close();
    }
}