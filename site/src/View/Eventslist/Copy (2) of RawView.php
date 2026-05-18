<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_planjeagenda
 *
 * @copyright   Copyright (C) 2026 KoelmanLabs. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Eventslist;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

/**
 * Raw View class for exporting events to iCal format.
 *
 * @since  1.0.0
 */
class RawView extends HtmlView
{
    /**
     * Array of event rows passed from the view model layer.
     *
     * @var array
     */
    protected $rows;

    /**
     * Method to execute data retrieval and render the raw iCal transmission.
     *
     * @return  void
     * @since   1.0.0
     */
    public function display($tpl = null): void
    {
        $this->rows = $this->get('Items');
        
        if (ob_get_length()) {
            ob_clean();
        }
        
        $helper = \KoelmanLabs\Component\Planjeagenda\Site\Helper\IcalHelper::getCalendarTool();
        
        if (!empty($this->rows) && is_array($this->rows)) {
            foreach ($this->rows as $row) {
                
                $startDateStr = !empty($row->dates) ? $row->dates : null;
                
                if (empty($startDateStr) || $startDateStr === '0000-00-00') {
                    continue;
                }
                
                $endDateStr   = !empty($row->enddates) ? $row->enddates : $startDateStr;
                $startTimeStr = !empty($row->times) ? $row->times : null;
                $endTimeStr   = !empty($row->endtimes) ? $row->endtimes : null;
                
                if (!empty($startTimeStr) && empty($endTimeStr)) {
                    $endTimeStr = $startTimeStr;
                }
                
                // Determine true structural rules
                $isAllDay   = empty($startTimeStr) && empty($endTimeStr);
                $isMultiDay = ($startDateStr !== $endDateStr);

                /**
                 * OPTION 1: MULTI-DAY TIMED EVENT SPLITTER
                 */
                if ($isMultiDay && !$isAllDay) {
                    try {
                        $startTimeStr = $startTimeStr ?? '00:00:00';
                        $endTimeStr   = $endTimeStr ?? '23:59:59';

                        $startPeriod = new \DateTime($startDateStr);
                        $endPeriod   = new \DateTime($endDateStr);
                        $endPeriod->modify('+1 day'); 

                        $interval = new \DateInterval('P1D');
                        $period   = new \DatePeriod($startPeriod, $interval, $endPeriod);

                        $startParts = explode(':', $startTimeStr);
                        $endParts   = explode(':', $endTimeStr);

                        foreach ($period as $date) {
                            $currentDateStr = $date->format('Y-m-d');
                            $dayUid = 'event-' . $row->id . '-' . $currentDateStr . '@planjeagenda';
                            
                            $dtStartArray = [
                                'year'  => (int) $date->format('Y'),
                                'month' => (int) $date->format('m'),
                                'day'   => (int) $date->format('d'),
                                'hour'  => (int) ($startParts[0] ?? 0),
                                'min'   => (int) ($startParts[1] ?? 0),
                                'sec'   => (int) ($startParts[2] ?? 0),
                            ];

                            $dtEndArray = [
                                'year'  => (int) $date->format('Y'),
                                'month' => (int) $date->format('m'),
                                'day'   => (int) $date->format('d'),
                                'hour'  => (int) ($endParts[0] ?? 0),
                                'min'   => (int) ($endParts[1] ?? 0),
                                'sec'   => (int) ($endParts[2] ?? 0),
                            ];

                            $eventProps = [
                                'uid'         => $dayUid,
                                'summary'     => $row->title ?? 'Untitled Event',
                                'description' => strip_tags($row->introtext ?? ''),
                                'dtstart'     => [
                                    'value'  => $dtStartArray,
                                    'params' => []
                                ],
                                'dtend'       => [
                                    'value'  => $dtEndArray,
                                    'params' => []
                                ],
                            ];

                            if (!empty($row->venue)) {
                                $eventProps['location'] = $row->venue . (!empty($row->city) ? ', ' . $row->city : '');
                            }

                            $helper->addEvent($eventProps);
                        }
                        continue;
                    } catch (\Exception $e) {
                        // Fallback safely
                    }
                }
                
                /**
                 * OPTION 2: STANDARD / ALL-DAY SINGLE OR MULTI-DAY PROCESSING
                 */
                if ($isAllDay) {
                    $dateParams = ['VALUE' => 'DATE'];
                    
                    // Use the string variables that are already extracted from $row earlier in the loop
                    $pureStartStr = trim((string) $startDateStr);
                    $pureEndStr   = trim((string) ($endDateStr ?? $startDateStr));
                    
                    try {
                        // Create fully isolated instances to prevent timezone leakage
                        $dtStartObj = new \DateTimeImmutable($pureStartStr, new \DateTimeZone('UTC'));
                        $dtEndObj   = new \DateTimeImmutable($pureEndStr, new \DateTimeZone('UTC'));
                        
                        // Crucial: All-day events require DTEND to be exclusive (+1 day)
                        $dtEndObjExclusive = $dtEndObj->modify('+1 day');
                        
                        // Validate layout logic before passing data to iCalcreator
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
                        // Resilient Fallback parsing strings directly if DateTime fails
                        $startYear  = (int) substr($pureStartStr, 0, 4);
                        $startMonth = (int) substr($pureStartStr, 5, 2);
                        $startDay   = (int) substr($pureStartStr, 8, 2);
                        
                        $dtStartArray = ['year' => $startYear, 'month' => $startMonth, 'day' => $startDay];
                        $dtEndArray   = ['year' => $startYear, 'month' => $startMonth, 'day' => $startDay + 1];
                    }
                    
                    $uid = 'event-' . $row->id . '-' . ($row->alias ?? 'event') . '@planjeagenda';
                    
                    $eventProps = [
                        'uid'         => $uid,
                        'summary'     => $row->title ?? 'Untitled Event',
                        'description' => strip_tags($row->introtext ?? ''),
                        'dtstart'     => [
                            'value'  => $dtStartArray,
                            'params' => $dateParams
                        ],
                        'dtend'       => [
                            'value'  => $dtEndArray,
                            'params' => $dateParams
                        ],
                    ];
                    
                    if (!empty($row->venue)) {
                        $eventProps['location'] = $row->venue . (!empty($row->city) ? ', ' . $row->city : '');
                    }
                    
                    $helper->addEvent($eventProps);
                    continue; // Step out to next row safely
                    
                } else {
                    // Keep your existing working logic for Timed Events down here...
                    $startTimeStr = $startTimeStr ?? '00:00:00';
                    $endTimeStr   = $endTimeStr ?? '23:59:59';
                    $dateParams   = [];
                    
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
                }
                
                // Chronological Correction Guard (Now accounts for the advanced all-day end state)
                $checkStart = $startDateStr . ' ' . $startTimeStr;
                $checkEnd   = $endDateStr . ' ' . $endTimeStr;
                
                if (strtotime($checkEnd) < strtotime($checkStart)) {
                    $endDateStr = $startDateStr;
                    $endTimeStr = $startTimeStr !== '00:00:00' ? $startTimeStr : '23:59:59';
                }
                
                $startParts = explode(':', $startTimeStr);
                $endParts   = explode(':', $endTimeStr);
                
                $dtStartArray = [
                    'year'  => (int) substr($startDateStr, 0, 4),
                    'month' => (int) substr($startDateStr, 5, 2),
                    'day'   => (int) substr($startDateStr, 8, 2),
                ];
                
                $dtEndArray = [
                    'year'  => (int) substr($endDateStr, 0, 4),
                    'month' => (int) substr($endDateStr, 5, 2),
                    'day'   => (int) substr($endDateStr, 8, 2),
                ];

                if (!$isAllDay) {
                    $dtStartArray['hour'] = (int) ($startParts[0] ?? 0);
                    $dtStartArray['min']  = (int) ($startParts[1] ?? 0);
                    $dtStartArray['sec']  = (int) ($startParts[2] ?? 0);

                    $dtEndArray['hour'] = (int) ($endParts[0] ?? 0);
                    $dtEndArray['min']  = (int) ($endParts[1] ?? 0);
                    $dtEndArray['sec']  = (int) ($endParts[2] ?? 0);
                }
                
                $uid = 'event-' . $row->id . '-' . ($row->alias ?? 'event') . '@planjeagenda';
                
                $eventProps = [
                    'uid'         => $uid,
                    'summary'     => $row->title ?? 'Untitled Event',
                    'description' => strip_tags($row->introtext ?? ''),
                    'dtstart'     => [
                        'value'  => $dtStartArray,
                        'params' => $dateParams
                    ],
                    'dtend'       => [
                        'value'  => $dtEndArray,
                        'params' => $dateParams
                    ],
                ];
                
                if (!empty($row->venue)) {
                    $eventProps['location'] = $row->venue . (!empty($row->city) ? ', ' . $row->city : '');
                }
                
                $helper->addEvent($eventProps);
            }
        }
        
        $helper->setConfig('filename', 'planjeagenda-events.ics');
        $helper->send();
        exit;
    }
}