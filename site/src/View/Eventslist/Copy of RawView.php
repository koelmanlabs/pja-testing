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
        // 1. Fetch data directly from the model layer
        $this->rows = $this->get('Items');

        // Suppress and wipe out any accidental white-space buffer artifacts
        if (ob_get_length()) {
            ob_clean();
        }

        // Initialize the native Planjeagenda iCal tool wrapper layer
        $helper = \KoelmanLabs\Component\Planjeagenda\Site\Helper\IcalHelper::getCalendarTool();

        if (!empty($this->rows) && is_array($this->rows)) {
            foreach ($this->rows as $row) {
                
                // 2. Safe base date string extraction
                $startDateStr = !empty($row->dates) ? $row->dates : null;
                
                // If there are no event dates set, ignore it completely (skip anomalies 0 & 1)
                if (empty($startDateStr) || $startDateStr === '0000-00-00') {
                    continue;
                }

                $endDateStr   = !empty($row->enddates) ? $row->enddates : $startDateStr;
                $startTimeStr = !empty($row->times) ? $row->times : '00:00:00';
                $endTimeStr   = !empty($row->endtimes) ? $row->endtimes : '23:59:59';

                // 3. Chronological Correction Guard (Prevents inverted time errors)
                $checkStart = $startDateStr . ' ' . $startTimeStr;
                $checkEnd   = $endDateStr . ' ' . $endTimeStr;

                if (strtotime($checkEnd) < strtotime($checkStart)) {
                    // Fallback: Clamp the end date to match the start date bounds perfectly
                    $endDateStr = $startDateStr;
                    $endTimeStr = '23:59:59';
                }

                // 4. Break down into explicit arrays to satisfy IcalHelper::buildDateTime()
                $startParts = explode(':', $startTimeStr);
                $endParts   = explode(':', $endTimeStr);

                $dtStartArray = [
                    'year'  => (int) substr($startDateStr, 0, 4),
                    'month' => (int) substr($startDateStr, 5, 2),
                    'day'   => (int) substr($startDateStr, 8, 2),
                    'hour'  => (int) ($startParts[0] ?? 0),
                    'min'   => (int) ($startParts[1] ?? 0),
                    'sec'   => (int) ($startParts[2] ?? 0),
                ];

                $dtEndArray = [
                    'year'  => (int) substr($endDateStr, 0, 4),
                    'month' => (int) substr($endDateStr, 5, 2),
                    'day'   => (int) substr($endDateStr, 8, 2),
                    'hour'  => (int) ($endParts[0] ?? 0),
                    'min'   => (int) ($endParts[1] ?? 0),
                    'sec'   => (int) ($endParts[2] ?? 0),
                ];

                // 5. Construct payload structure using the helper's array keys
                $uid = 'event-' . $row->id . '-' . ($row->alias ?? 'event') . '@planjeagenda';

                $eventProps = [
                    'uid'         => $uid,
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
        }

        // Set the safe filename context matching your extension standards
        $helper->setConfig('filename', 'planjeagenda-events.ics');

        // Execute streaming file delivery using your helper's native send routine
        $helper->send();

        // Halt Joomla processing cleanly
        exit;
    }
}