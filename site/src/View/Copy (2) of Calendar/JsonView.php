<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 * 
 * 
 * Calendar JSON view — serves events to FullCalendar via AJAX
 * URL: index.php?option=com_planjeagenda&view=calendar&format=json
 *      &start=2026-05-01T00:00:00&end=2026-06-01T00:00:00
 *
 * @package  KoelmanLabs\Component\Planjeagenda
 */
namespace KoelmanLabs\Component\Planjeagenda\Site\View\Calendar;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonView as BaseJsonView;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\Router\Route;

class JsonView extends BaseJsonView
{
    public function display($tpl = null): void
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        $db    = Factory::getContainer()->get('DatabaseDriver');

        // FullCalendar sends ISO timestamps like 2026-05-01T00:00:00
        // We only need the date portion
        $start = substr($input->getString('start', date('Y-m-01')), 0, 10);
        $end   = substr($input->getString('end',   date('Y-m-t')),  0, 10);

        // Sanitise: dates only
        $start = preg_replace('/[^0-9\-]/', '', $start);
        $end   = preg_replace('/[^0-9\-]/', '', $end);

        $user   = $app->getIdentity();
        $levels = implode(',', $user->getAuthorisedViewLevels());

        try {
            $query = $db->getQuery(true)
                ->select([
                    'a.id', 'a.title', 'a.alias', 'a.dates', 'a.enddates',
                    'a.times', 'a.endtimes', 'a.introtext',
                    'c.color AS cat_color', 'c.catname',
                    'v.venue', 'v.city',
                    "CONCAT(a.id,':',a.alias) AS slug",
                ])
                ->from('#__pja_events AS a')
                ->join('LEFT', '#__pja_categories AS c ON c.id = a.catid')
                ->join('LEFT', '#__pja_venues AS v ON v.id = a.locid')
                ->where('a.published = 1')
                ->where('a.access IN (' . $levels . ')')
                ->where('a.dates >= ' . $db->quote($start))
                ->where('a.dates <= ' . $db->quote($end));

            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->sendJson([]);
            return;
        }

        $events = [];
        foreach ($rows as $row) {
            $color = $row->cat_color ?: '#1565c0';
            if ($color[0] !== '#') {
                $color = '#' . $color;
            }

            $startDt = $row->dates;
            $endDt   = $row->enddates ?: $row->dates;

            // Include time if set and non-midnight
            if ($row->times && $row->times !== '00:00:00') {
                $startDt .= 'T' . $row->times;
                $endDt   .= 'T' . (($row->endtimes && $row->endtimes !== '00:00:00')
                    ? $row->endtimes : $row->times);
            }

            $events[] = [
                'id'            => (int) $row->id,
                'title'         => $row->title,
                'start'         => $startDt,
                'end'           => $endDt,
                'color'         => $color,
                'textColor'     => '#ffffff',
                'url'           => Route::_(
                    'index.php?option=com_planjeagenda&view=event&id=' . $row->slug
                ),
                'extendedProps' => [
                    'category' => $row->catname ?? '',
                    'venue'    => trim(($row->venue ?? '') . ($row->city ? ', ' . $row->city : '')),
                    'intro'    => strip_tags(substr($row->introtext ?? '', 0, 150)),
                ],
            ];
        }

        $this->sendJson($events);
    }

    private function sendJson(array $data): void
    {
        // Suppress any output buffered so far
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
