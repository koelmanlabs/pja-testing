<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

/**
 * Event-Raw
 */
class PlanjeagendaViewEvent extends HtmlView
{
    /**
     * Creates the output for the event view
     */
    public function display($tpl = null)
    {
        $settings = PlanjeagendaHelper::globalattribs();

        // check iCal global setting
        if ($settings->get('global_show_ical_icon','0')==1) {
            // Get data from the model
            $row = $this->get('Item');

            if (empty($row)) {
                return;
            }

            $row->categories = $this->get('Categories');
            $row->id         = $row->did;
            $row->slug       = $row->alias ? ($row->id.':'.$row->alias) : $row->id;
            $params          = $row->params;

            // check individual iCal Event setting
            if ($params->get('event_show_ical_icon',1)) {
                // initiate new CALENDAR
                $vcal = PlanjeagendaHelper::getCalendarTool();
                $vcal->setConfig( "filename", "event".$row->did.".ics" );
                PlanjeagendaHelper::icalAddEvent($vcal, $row);
                // generate and redirect output to user browser
                $vcal->send();
            }
        }
    }
}
