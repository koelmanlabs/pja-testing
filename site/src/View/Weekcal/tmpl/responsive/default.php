<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
?>

<div id="klevents" class="jlcalendar jem_calendar<?php echo $this->pageclass_sfx;?>">
    <div class="buttons">
        <?php
        // Modern action bar
        ?>
        <div class="pja-ev-actionbar" style="margin-bottom:1rem;">
            <div class="pja-ev-actionbar__left"></div>
            <div class="pja-ev-actionbar__right">
                <?php if (!empty($btn_params['print_link'] ?? '')): ?>
                <a href="<?php echo $btn_params['print_link']; ?>"
                   class="pja-ev-action-icon"
                   title="<?php echo Text::_('JGLOBAL_PRINT'); ?>"
                   onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480');return false;"
                   aria-label="<?php echo Text::_('JGLOBAL_PRINT'); ?>">
                    <svg width="15" height="15" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M128 0C92.7 0 64 28.7 64 64v96h64V64H354.7L384 93.3V160h64V93.3c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0H128zM384 352v32 64H128V384 368 352H384zm64 32h32c17.7 0 32-14.3 32-32V256c0-35.3-28.7-64-64-64H64c-35.3 0-64 28.7-64 64v96c0 17.7 14.3 32 32 32H64v64c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V384z"/></svg>
                </a>
                <?php endif; ?>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=eventslist&format=raw&layout=ics'); ?>"
                   class="pja-ev-action-icon"
                   title="<?php echo Text::_('com_planjeagenda_ICAL'); ?>">
                    <svg width="15" height="15" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/></svg>
                    <span>iCal</span>
                </a>
            </div>
        </div>
        <?php
        ?>
    </div>

    <?php if ($this->params->get('show_page_heading', 1)): ?>
        <h1 class="componentheading">
            <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
    <?php endif; ?>

    <?php if ($this->params->get('showintrotext')) : ?>
        <div class="description no_space floattext">
            <?php echo $this->params->get('introtext'); ?>
        </div>
        <p> </p>
    <?php endif; ?>

    <?php
    $countcatevents = array ();
    $countperday = array();
    $limit = $this->params->get('daylimit', 10);
    $evbg_usecatcolor = $this->params->get('eventbg_usecatcolor', 0);
    $currentWeek = $this->currentweek;
    $firstDate = date("Y-m-d", $this->cal->getFirstDayTimeOfWeek($currentWeek));
    $recurrenceIconRender = $this->params->get('recurrenceIconRender', 0);
    $showtime = $this->settings->get('global_show_timedetails', 1);
    $categoryColorMarker = $this->params->get('categoryColorMarker', 0);

    foreach ($this->rows as $row) :
        if (!PlanjeagendaHelper::isValidDate($row->dates)) {
            continue; // skip, open date !
        }

        // has user access
        $eventaccess = '';
        if (!$row->user_has_access_event) {
            // show a closed lock icon
            $eventaccess = ' <span class="icon-lock klevents-lockicon" aria-hidden="true"></span>';
        }

        //get event date
        $year = date('Y', strtotime($row->dates));
        $month = date('m', strtotime($row->dates));
        $day = date('d', strtotime($row->dates));

        @$countperday[$year.$month.$day]++;
        if ($countperday[$year.$month.$day] == $limit+1) {
            $var1a = Route::_('index.php?option=com_planjeagenda&view=day&id='.$year.$month.$day . $this->param_topcat);
            $var1b = Text::_('com_planjeagenda_AND_MORE');
            $var1c = "<a href=\"".$var1a."\">".$var1b."</a>";
            $id = 'eventandmore';

            $this->cal->setEventContent($year, $month, $day, $var1c, null, $id);
            continue;
        } elseif ($countperday[$year.$month.$day] > $limit+1) {
            continue;
        }

        //for time in tooltip
        $timehtml = '';

        if ($showtime) {
            $start = PlanjeagendaOutput::formattime($row->times);
            $end = PlanjeagendaOutput::formattime($row->endtimes);

            if ($start != '') {
                $timehtml = '<div class="time"><span class="text-label">'.Text::_('com_planjeagenda_TIME_SHORT').': </span>';
                $timehtml .= $start;
                if ($end != '') {
                    $timehtml .= ' - '.$end;
                }
                $timehtml .= '</div>';
            }
        }

        $eventname  = '<div class="eventName">'.Text::_('com_planjeagenda_TITLE_SHORT').': '.$this->escape($row->title).'</div>';
        $detaillink = Route::_(PlanjeagendaHelperRoute::getEventRoute($row->slug));
        $eventid = $this->escape($row->id);

        //Contact
        $contactname = '';
        if($row->contactid) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('name');
            $query->from('#__contact_details');
            $query->where(array('id='.(int)$row->contactid));
            $db->setQuery($query);
            $contactname = $db->loadResult();
        }
        if ($contactname) {
            $contact  = '<div class="contact"><span class="text-label">'.Text::_('com_planjeagenda_CONTACT').': </span>';
            $contact .=     !empty($contactname) ? $this->escape($contactname) : '-';
            $contact .= '</div>';
        } else {
            $contact = '';
        }

        //initialize variables
        $multicatname = '';
        $colorpic = '';
        $nr = is_array($row->categories) ? count($row->categories) : 0;
        $ix = 0;
        $content = '';
        $contentend = '';
        $catcolor = array();

        //walk through categories assigned to an event
        $catcolor = array();

        foreach((array)$row->categories AS $category) {
            // Currently only one id possible...so simply just pick one up...
            $detaillink = Route::_(PlanjeagendaHelperRoute::getEventRoute($row->slug));

            // Wrap a div for each category around the event for show/hide toggler
            $content    .= '<div id="catz" class="cat'.$category->id.'">';
            $contentend .= '</div>';

            // Attach category color in front of the catname
            if ($category->color) {
                $multicatname .= '<span class="colorpicblock" style="background-color: '.$category->color.';"></span>&nbsp;'.$category->catname;
            } else {
                $multicatname .= $category->catname;
            }

            $ix++;
            if ($ix != $nr) {
                $multicatname .= ', ';
            }

            // Collect all category colors (needed for the bar or blocks)
            if (isset($category->color) && $category->color) {
                $catcolor[$category->color] = $category->color;
            }

            // Count category occurrence
            if (!isset($row->multi) || ($row->multi == 'first')) {
                if (!array_key_exists($category->id, $countcatevents)) {
                    $countcatevents[$category->id] = 1;
                } else {
                    $countcatevents[$category->id]++;
                }
            }
        }

        // Build color output depending on $categoryColorMarker
        $color = ''; // default empty
        if (!empty($catcolor)) {
            if ($categoryColorMarker) {
                // Build a single multicolor TOP BAR
                $numColors = count($catcolor);
                $step = 100 / $numColors;
                $gradientParts = [];
                $i = 0;

                foreach ($catcolor as $color) {
                    $start = $i * $step;
                    $end = ($i + 1) * $step;
                    $gradientParts[] = "$color $start% $end%";
                    $i++;
                }

                $gradientCss = "linear-gradient(to right, " . implode(", ", $gradientParts) . ")";
                $color  = '<div id="eventcontenttop" class="eventcontenttop pt-0">';
                $color .= '<div class="colorpicbar" style="background: '.$gradientCss.';"></div>';
                $color .= '</div>';

            } else {
                // Build individual color BLOCKS
                $colorpic = '';
                foreach ($catcolor as $color) {
                    $colorpic .= '<span class="colorpicblock" style="background-color: '.$color.';"></span>';
                }
                $color = $colorpic;
            }
        }

        // multiday
        $multi_mode = 0; // single day
        $multi_icon = '';
        if (isset($row->multi)) {
            switch ($row->multi) {
                case 'first': // first day
                    $multi_mode = 1;
                    if($recurrenceIconRender){
                        $multi_icon = HTMLHelper::_("image","com_planjeagenda/arrow-left.webp",'', NULL, true);
                    }else{
                        $multi_icon = '<i class="fa fa-step-backward" aria-hidden="true"></i>';
                    }
                    break;
                case 'middle': // middle day
                    $multi_mode = 2;
                    if($recurrenceIconRender){
                        $multi_icon = HTMLHelper::_("image","com_planjeagenda/arrow-middle.webp",'', NULL, true);
                    }else{
                        $multi_icon = '<i class="fas fa-arrows-alt-h"></i>';
                    }
                    break;
                case 'zlast': // last day
                    $multi_mode = 3;
                    if($recurrenceIconRender){
                        $multi_icon = HTMLHelper::_("image","com_planjeagenda/arrow-right.webp",'', NULL, true);
                    }else{
                        $multi_icon = '<i class="fa fa-step-forward" aria-hidden="true"></i>';
                    }
                    break;
            }
        }

        //for time in calendar
        $timetp = '';

        if ($showtime) {
            $start = PlanjeagendaOutput::formattime($row->times,'',false);
            $end   = PlanjeagendaOutput::formattime($row->endtimes,'',false);

            switch ($multi_mode) {
                case 1:
                    $timetp .= $multi_icon . ' ' . $start . '<br>';
                    break;
                case 2:
                    $timetp .= $multi_icon . '<br>';
                    break;
                case 3:
                    $timetp .= $multi_icon . ' ' . $end . '<br>';
                    break;
                default:
                    if ($start != '') {
                        $timetp .= $start;
                        if ($end != '') {
                            $timetp .= ' - '.$end;
                        }
                        $timetp .= '<br>';
                    }
                    break;
            }
        } else {
            if (!empty($multi_icon)) {
                $timetp .= $multi_icon . ' ';
            }
        }

        $catname = '<div class="catname">'.$multicatname.'</div>';

        $eventdate = !empty($row->multistartdate) ? PlanjeagendaOutput::formatdate($row->multistartdate) : PlanjeagendaOutput::formatdate($row->dates);
        if (!empty($row->multienddate)) {
            $eventdate .= ' - ' . PlanjeagendaOutput::formatdate($row->multienddate);
        } else if ($row->enddates && $row->dates < $row->enddates) {
            $eventdate .= ' - ' . PlanjeagendaOutput::formatdate($row->enddates);
        }

        //venue
        if ($this->jemsettings->showlocate == 1) {
            $venue  = '<div class="location"><span class="text-label">'.Text::_('com_planjeagenda_VENUE_SHORT').': </span>';
            $venue .=     !empty($row->venue) ? $this->escape($row->venue) : '-';
            $venue .= '</div>';
        } else {
            $venue = '';
        }

        // state if unpublished
        $statusicon = '';
        if (isset($row->published) && ($row->published != 1)) {
            $statusicon  = PlanjeagendaOutput::publishstateicon($row);
            $eventstate  = '<div class="eventstate"><span class="text-label">'.Text::_('JSTATUS').': </span>';
            switch ($row->published) {
                case  1: $eventstate .= Text::_('JPUBLISHED');   break;
                case  0: $eventstate .= Text::_('JUNPUBLISHED'); break;
                case  2: $eventstate .= Text::_('JARCHIVED');    break;
                case -2: $eventstate .= Text::_('JTRASHED');     break;
            }
            $eventstate .= '</div>';
        } else {
            $eventstate  = '';
        }

        // has user access
        $eventaccess = "";
        if(!$row->user_has_access_event){
            // show a closed lock icon
            $statusicon  = PlanjeagendaOutput::publishstateicon($row);
            $eventaccess  = '<span class="icon-lock" style="margin-left:5px;" aria-hidden="true"></span>';
        }

        //date in tooltip
        $multidaydate = '<div class="time"><span class="text-label">'.Text::_('com_planjeagenda_DATE').': </span>';
        switch ($multi_mode) {
            case 1:  // first day
                $multidaydate .= PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times, $row->enddates, $row->endtimes, $showtime);
                $multidaydate .= PlanjeagendaOutput::formatSchemaOrgDateTime($row->dates, $row->times, $row->enddates, $row->endtimes);
                break;
            case 2:  // middle day
                $multidaydate .= PlanjeagendaOutput::formatShortDateTime($row->multistartdate, $row->times, $row->multienddate, $row->endtimes, $showtime);
                $multidaydate .= PlanjeagendaOutput::formatSchemaOrgDateTime($row->multistartdate, $row->times, $row->multienddate, $row->endtimes);
                break;
            case 3:  // last day
                $multidaydate .= PlanjeagendaOutput::formatShortDateTime($row->multistartdate, $row->times, $row->multienddate, $row->endtimes, $showtime);
                $multidaydate .= PlanjeagendaOutput::formatSchemaOrgDateTime($row->multistartdate, $row->times, $row->multienddate, $row->endtimes);
                break;
            default: // single day
                $multidaydate .= PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times, $row->enddates, $row->endtimes, $showtime);
                $multidaydate .= PlanjeagendaOutput::formatSchemaOrgDateTime($row->dates, $row->times, $row->enddates, $row->endtimes);
                break;
        }
        $multidaydate .= '</div>';

        //create little Edit and/or Copy icon on top right corner of event if user is allowed to edit and/or create
        $editicon = '';
        if (!$this->print) {
            $btns = array();
            if ($this->params->get('show_editevent_icon', 0) && $row->params->get('access-edit', false)) {
                $btns[] = PlanjeagendaOutput::editbutton($row, null, null, true, 'editevent');
            }
            if ($this->params->get('show_copyevent_icon', 0) && $this->permissions->canAddEvent) {
                $btns[] = PlanjeagendaOutput::copybutton($row, null, null, true, 'editevent');
            }
            if (!empty($btns)) {
                $editicon .= '<div class="inline-button-right">';
                $editicon .= join(' ', $btns);
                $editicon .= '</div>';
            }
        }

        //get border for featured event
        $usefeaturedborder = $this->params->get('usefeaturedborder', 0);
        $featuredbordercolor = $this->params->get('featuredbordercolor', 0);
        $featuredclass = '';
        $featuredstyle ='';
        if($usefeaturedborder && $row->featured){
            $featuredclass="borderfeatured";
            $featuredstyle="border-color:" . $featuredbordercolor;
        }

        //generate the output
        // if we have exact one color from categories we can use this as background color of event
        $content .= '<div class="eventcontentinner event_id' . $eventid . ' cat_id' . $category->id . ' ' . $featuredclass . ($categoryColorMarker ? ' pt-0 pb-2' : '') . '" style="' . $featuredstyle;
        $style = '';
        if (!empty($evbg_usecatcolor) && count($catcolor) === 1) {
            $style = '; background-color:' . array_pop($catcolor);
        }
        $content .= $style . '" onclick="location.href=\'' . $detaillink . '\'">';
        $divClass = $categoryColorMarker ? 'eventcontenttextbar' : 'eventcontenttextblock';
        $content .= '<div class="' . $divClass . '">';
        if (empty($evbg_usecatcolor) || count($catcolor) !== 1) {
            $content .= $color;
        }

        $content .= $editicon;
        $content .= PlanjeagendaHelper::caltooltip($catname.$eventname.$timehtml.$venue.$contact.$eventstate, $eventdate, $row->title . $statusicon, $detaillink, 'editlinktip hasTip', $timetp, $category->color);
        $content .= $eventaccess . $contentend . '</div></div>';

        $this->cal->setEventContent($year, $month, $day, $content);
    endforeach;

    // enable little icon right beside day number to allow event creation
    if (!$this->print && $this->params->get('show_addevent_icon', 0) && !empty($this->permissions->canAddEvent)) {
        $html = PlanjeagendaOutput::prepareAddEventButton();
        $this->cal->enableNewEventLinks($html);
    }

    $displayLegend = (int)$this->params->get('displayLegend', 1);
    if ($displayLegend == 2) : ?>
        <!-- Calendar legend above -->
        <div id="jlcalendarlegend">

            <!-- Calendar buttons -->
            <div class="calendarButtons klevents-row klevents-justify-start">
                <button id="buttonshowall" class="calendarButton btn btn-outline-dark">
                    <?php echo Text::_('com_planjeagenda_SHOWALL'); ?>
                </button>
                <button id="buttonhideall" class="calendarButton btn btn-outline-dark">
                    <?php echo Text::_('com_planjeagenda_HIDEALL'); ?>
                </button>
            </div>

            <!-- Calendar Legend -->
            <div class="calendarLegends klevents-row klevents-justify-start">
                <?php
                if ($this->params->get('displayLegend')) {

                    ##############
                    ## FOR EACH ##
                    ##############

                    $counter = array();

                    # walk through events
                    foreach ($this->rows as $row) {
                        foreach ($row->categories as $cat) {

                            # sort out dupes for the counter (catid-legend)
                            if (!in_array($cat->id, $counter)) {
                                # add cat id to cat counter
                                $counter[] = $cat->id;

                                # build legend
                                if (array_key_exists($cat->id, $countcatevents)) {
                                    ?>
                                    <button class="eventCat btn btn-outline-dark" id="cat<?php echo $cat->id; ?>">
                                        <?php
                                        if (!empty($cat->color)) {
                                            $class = $categoryColorMarker ? 'colorpicbar' : 'colorpicblock';
                                            echo '<span class="' . $class . '" style="background-color:' . $cat->color . ';"></span>';
                                        }
                                        echo $cat->catname . ' (' . $countcatevents[$cat->id] . ')';
                                        ?>
                                    </button>
                                    <?php
                                }
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // print the calendar
    $nrweeks = $this->params->get('nrweeks', 1);
    echo $this->cal->showWeeksByID($currentWeek, $nrweeks);
    ?>

    <?php if (($displayLegend == 1) || ($displayLegend == 0)) : ?>
        <!-- Calendar legend below -->
        <div id="jlcalendarlegend">

            <!-- Calendar buttons -->
            <div class="calendarButtons klevents-row klevents-justify-start">
                <button id="buttonshowall" class="calendarButton btn btn-outline-dark">
                    <?php echo Text::_('com_planjeagenda_SHOWALL'); ?>
                </button>
                <button id="buttonhideall" class="calendarButton btn btn-outline-dark">
                    <?php echo Text::_('com_planjeagenda_HIDEALL'); ?>
                </button>
            </div>

            <!-- Calendar Legend -->
            <div class="calendarLegends klevents-row klevents-justify-start">
                <?php
                if ($displayLegend == 1) {

                    ##############
                    ## FOR EACH ##
                    ##############

                    $counter = array();

                    # walk through events
                    foreach ($this->rows as $row) {
                        foreach ($row->categories as $cat) {

                            # sort out dupes for the counter (catid-legend)
                            if (!in_array($cat->id, $counter)) {
                                # add cat id to cat counter
                                $counter[] = $cat->id;

                                # build legend
                                if (array_key_exists($cat->id, $countcatevents)) {
                                    ?>
                                    <button class="eventCat btn btn-outline-dark me-2" id="cat<?php echo $cat->id; ?>">
                                        <?php
                                        if (!empty($cat->color)) {
                                            $class = $categoryColorMarker ? 'colorpicbar' : 'colorpicblock ms-2';
                                            echo '<span class="' . $class . '" style="background-color:' . $cat->color . ';"></span>';
                                        }

                                        $text = $cat->catname . ' (' . $countcatevents[$cat->id] . ')';
                                        $textClass = $categoryColorMarker ? 'colorpicbartext' : 'colorpicblocktext pe-2';
                                        echo '<span class="' . $textClass . '">' . $text . '</span>';
                                        ?>
                                    </button>
                                    <?php
                                }
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="clr"></div>

    <div class="copyright">
        <?php echo PlanjeagendaOutput::footer(); ?>
    </div>
</div>
