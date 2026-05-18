<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$colspan = ($this->event->waitinglist ? 10 : 9);

$detaillink = Route::_(PlanjeagendaHelperRoute::getEventRoute($this->event->id.':'.$this->event->alias));

$namefield = $this->settings->get('global_regname', '1') ? 'name' : 'username';
$namelabel = $this->settings->get('global_regname', '1') ? 'com_planjeagenda_NAME' : 'com_planjeagenda_USERNAME';

?>
<script>
    function tableOrdering(order, dir, view)
    {
        var form = document.getElementById("adminForm");

        form.filter_order.value     = order;
        form.filter_order_Dir.value    = dir;
        form.submit(view);
    }
</script>
<script>
    function jSelectUsers_newusers(ids, count, status, places, eventid, seriesbooking, token) {
        document.location.href = 'index.php?option=com_planjeagenda&task=attendees.attendeeadd&id='+eventid+'&status='+status+'&places='+places+'&uids='+ids+'&series='+seriesbooking+'&'+token+'=1';
        SqueezeBox.close();
    }
</script>

<div id="klevents" class="jem_attendees <?php echo $this->pageclass_sfx;?>">
    <div class="buttons">
        <?php
        $permissions = new stdClass();
        $permissions->canAddUsers = true;
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

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1 class="componentheading">
            <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
    <?php endif; ?>

    <div class="clr"></div>

    <?php if ($this->params->get('showintrotext')) : ?>
        <div class="description no_space floattext">
            <?php echo $this->params->get('introtext'); ?>
        </div>
    <?php endif; ?>

    <h2><?php echo $this->escape($this->event->title); ?></h2>

    <form action="<?php echo htmlspecialchars($this->action); ?>"  method="post" name="adminForm" id="adminForm">
        <dl class="klevents-dl">
            <dt class="klevents-title"><?php echo Text::_('com_planjeagenda_TITLE').':'; ?></dt>
                <a href="<?php echo $detaillink ; ?>"><?php echo $this->escape($this->event->title); ?></a> <?php echo $this->event->recurrence_type? '<i class="fa fa-fw fa-refresh klevents-recurrenceicon"></i>':'' ?>
            <dt class="klevents-date"><?php echo Text::_('com_planjeagenda_DATE').':'; ?></dt>
            <dd class="klevents-date">
                <?php echo PlanjeagendaOutput::formatLongDateTime($this->event->dates, $this->event->times, $this->event->enddates, $this->event->endtimes, $this->settings->get('global_show_timedetails', 1)); ?>
            </dd>
        </dl>
        <div id="jem_filter" class="klevents-dl">
            <div class="row klevents-row">
                <div class="col-md-2">
                    <div class="row">
                        <div class="wauto-minwmax">
                            <div class="input-group">
                                <?php echo '<label for="filter_search">'.Text::_('com_planjeagenda_SEARCH').'</label>'; ?>
                                <?php echo $this->lists['filter']; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row mb-12">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" name="filter_search" id="filter_search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>" class="inputbox" onChange="document.adminForm.submit();" />
                                <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                                <button class="btn btn-secondary" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group" style="margin-top:6px;">
                                <?php echo '<label for="filter_status">'.Text::_('com_planjeagenda_STATUS').'</label>'; ?>
                                <?php echo $this->lists['status']; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="row ">
                        <div class="wauto-minwmax">
                            <div class=" float-end">
                                <?php echo $this->pagination?->getLimitBox(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (empty($this->rows)) : ?>
                <div style="padding-bottom: 8px;">
                    <strong><i><?php echo Text::_('com_planjeagenda_ATTENDEES_EMPTY_YET'); ?></i></strong>
                </div>
             <?php endif;?>
        </div>

        <div class="klevents-sort klevents-sort-small" id="articleList">
            <div class="klevents-list-row klevents-small-list">
                <div class="sectiontableheader klevents-attendee-number"><?php echo Text::_('com_planjeagenda_NUM'); ?></div>
                <div class="sectiontableheader klevents-attendee-name"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_USERNAME', 'u.'.$namefield, $this->lists['order_Dir'], $this->lists['order'] ); ?></div>
                <?php if ($this->enableemailaddress == 1) :?>
                    <div class="sectiontableheader klevents-attendee-email"><?php echo Text::_('com_planjeagenda_EMAIL'); ?></div>
                <?php endif; ?>
                <div class="sectiontableheader klevents-attendee-regdate"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_REGDATE', 'r.uregdate', $this->lists['order_Dir'], $this->lists['order'] ); ?></div>
                <div class="sectiontableheader klevents-attendee-status"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_STATUS', 'r.status', $this->lists['order_Dir'], $this->lists['order'] ); ?></div>
                <div class="sectiontableheader klevents-attendee-places"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_PLACES', 'r.places', $this->lists['order_Dir'], $this->lists['order'] ); ?></div>
                <?php if (!empty($this->jemsettings->regallowcomments)) : ?>
                    <div class="sectiontableheader klevents-attendee-comment"><?php echo Text::_('com_planjeagenda_COMMENT'); ?></div>
                <?php endif; ?>
                <div class="sectiontableheader klevents-attendee-remove"><?php echo Text::_('com_planjeagenda_REMOVE_USER'); ?></div>
            </div>
        </div>

        <ul class="eventlist eventtable">
            <?php $del_link = 'index.php?option=com_planjeagenda&view=attendees&task=attendees.attendeeremove&id='.$this->event->id.(!empty($this->item->id)?'&Itemid='.$this->item->id:'').'&'.Session::getFormToken().'=1';
            ?>
            <?php foreach ($this->rows as $i => $row) : ?>
                <li class="klevents-event klevents-list-row klevents-small-list row<?php echo $i % 2; ?>">
                    <div class="klevents-event-info-small klevents-attendee-number">
                        <?php echo $this->pagination->getRowOffset($i); ?>
                    </div>

                    <div class="klevents-event-info-small klevents-attendee-name">
                        <?php echo $row->$namefield; ?>
                    </div>

                    <?php if ($this->enableemailaddress == 1) :?>
                        <div class="klevents-event-info-small klevents-attendee-email">
                            <a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a>
                        </div>
                    <?php endif; ?>

                    <div class="klevents-event-info-small klevents-attendee-regdate">
                        <?php if (!empty($row->uregdate)) { echo HTMLHelper::_('date', $row->uregdate, Text::_('DATE_FORMAT_LC5')); } ?>
                    </div>

                    <div class="klevents-event-info-small klevents-attendee-status">
                        <?php
                        $status = (int)$row->status;
                        if($this->event->waitinglist) {
                            if ($status === 1 && $row->waiting == 1) { $status = 2; }
                            echo jemhtml::toggleAttendanceStatus($row->id, $status, true);
                        }else{
                            echo jemhtml::toggleAttendanceStatus($row->id, $status, false);
                        }
                        ?>
                    </div>
                    <div class="klevents-event-info-small klevents-attendee-places">
                        <?php echo $row->places; ?>
                    </div>

                    <?php if (!empty($this->jemsettings->regallowcomments)) : ?>
                        <?php $cmnt = (\Joomla\String\StringHelper::strlen($row->comment) > 16) ? (\Joomla\String\StringHelper::substr($row->comment, 0, 14).'&hellip;') : $row->comment; ?>
                        <div class="klevents-event-info-small klevents-attendee-comment">
                            <?php if (!empty($cmnt)) { echo HTMLHelper::_('tooltip', $row->comment, null, null, $cmnt, null, null); } ?>
                        </div>
                    <?php endif;?>

                    <div class="klevents-event-info-small klevents-attendee-remove">
                        <div class="center">
                            <a href="<?php echo Route::_($del_link.'&cid[]='.$row->id); ?>">
                                <?php echo PlanjeagendaOutput::removebutton(Text::_('com_planjeagenda_ATTENDEES_DELETE'), array('title' => Text::_('com_planjeagenda_ATTENDEES_DELETE'), 'class' => 'hasTooltip')); ?>
                            </a>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php echo HTMLHelper::_('form.token'); ?>
        <input type="hidden" name="option" value="com_planjeagenda" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="view" value="attendees" />
        <input type="hidden" name="id" value="<?php echo $this->event->id; ?>" />
        <input type="hidden" name="Itemid" value="<?php echo $this->item->id;?>" />
        <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
        <input type="hidden" name="enableemailaddress" value="<?php echo $this->enableemailaddress; ?>" />
    </form>

    <div class="pagination">
        <?php echo $this->pagination?->getPagesLinks(); ?>
    </div>

    <div class="copyright">
        <?php echo PlanjeagendaOutput::footer(); ?>
    </div>
</div>
