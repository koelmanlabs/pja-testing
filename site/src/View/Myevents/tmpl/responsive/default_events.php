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

?>

<?php if (!$this->params->get('show_page_heading', 1)) :
           /* hide this if page heading is shown */     ?>
<h2><?php echo \Text::_('com_planjeagenda_MY_EVENTS'); ?></h2>
<?php endif; ?>

<script>
    function tableOrdering(order, dir, view)
    {
        var form = document.getElementById("adminForm");

        form.filter_order.value     = order;
        form.filter_order_Dir.value    = dir;
        form.submit(view);
    }
</script>


<style>
  <?php if (!empty($this->jemsettings->tablewidth)) : ?>
    #klevents #adminForm {
      width: <?php echo ($this->jemsettings->tablewidth); ?>;
    }
  <?php endif; ?>

  .klevents-sort #jem_date,
  #klevents .klevents-event .klevents-event-date {
    <?php if (!empty($this->jemsettings->datewidth)) : ?>
      flex: 1 <?php echo intval(($this->jemsettings->datewidth))-4 . '%'; /*take a little off to fit status*/?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
    <?php if (\PlanjeagendaHelper::jemStringContains($this->pageclass_sfx, 'klevents-nodate')) : ?>
      display: none;
    <?php endif; ?>
  }

  .klevents-sort #jem_title,
  #klevents .klevents-event .klevents-event-title {
    <?php if (($this->jemsettings->showtitle == 1) && (!empty($this->jemsettings->titlewidth))) : ?>
      flex: 1 <?php echo (intval($this->jemsettings->titlewidth))-4 . '%'; /*take a little off to fit status*/?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
    <?php if (\PlanjeagendaHelper::jemStringContains($this->pageclass_sfx, 'klevents-notitle')) : ?>
      display: none;
    <?php endif; ?>
  }

  .klevents-sort #jem_location,
  #klevents .klevents-event .klevents-event-venue {
    <?php if (($this->jemsettings->showlocate == 1) && (!empty($this->jemsettings->locationwidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->locationwidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
    <?php if (\PlanjeagendaHelper::jemStringContains($this->pageclass_sfx, 'klevents-novenue')) : ?>
      display: none;
    <?php endif; ?>
  }

  .klevents-sort #jem_city,
  #klevents .klevents-event .klevents-event-city {
    <?php if (($this->jemsettings->showcity == 1) && (!empty($this->jemsettings->citywidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->citywidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
    <?php if (\PlanjeagendaHelper::jemStringContains($this->pageclass_sfx, 'klevents-nocity')) : ?>
      display: none;
    <?php endif; ?>
  }

  .klevents-sort #jem_state,
  #klevents .klevents-event .klevents-event-state {
    <?php if (($this->jemsettings->showstate == 1) && (!empty($this->jemsettings->statewidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->statewidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
    <?php if (\PlanjeagendaHelper::jemStringContains($this->pageclass_sfx, 'klevents-nostate')) : ?>
      display: none;
    <?php endif; ?>
  }

  .klevents-sort #jem_category,
  #klevents .klevents-event .klevents-event-category {
    <?php if (($this->jemsettings->showcat == 1) && (!empty($this->jemsettings->catfrowidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->catfrowidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
    <?php if (\PlanjeagendaHelper::jemStringContains($this->pageclass_sfx, 'klevents-nocategory')) : ?>
      display: none;
    <?php endif; ?>
  }

  .klevents-sort #jem_atte,
  #klevents .klevents-event .klevents-event-attendees {
    <?php if (($this->jemsettings->showatte == 1) && (!empty($this->jemsettings->attewidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->attewidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
    <?php if (\PlanjeagendaHelper::jemStringContains($this->pageclass_sfx, 'klevents-noattendees')) : ?>
      display: none;
    <?php endif; ?>
  }

  #klevents .klevents-event .klevents-myevents-check {
    flex: 0 1%;
  }

  #klevents .klevents-event .klevents-myevents-status {
    flex: 0 1%;
  }
</style>


<form action="<?php echo htmlspecialchars($this->action); ?>" method="post" name="adminForm" id="adminForm">
  <?php if ($this->settings->get('global_show_filter',1) || $this->settings->get('global_display',1)) : ?>
        <?php if ($this->settings->get('global_show_filter',1)) : ?>
        <div id="jem_filter" class="floattext klevents-form klevents-row klevents-justify-start">
        <div>
          <?php echo '<label for="filter">'.\Text::_('com_planjeagenda_FILTER').'</label>'; ?>
        </div>
        <div class="klevents-row klevents-justify-start klevents-nowrap">
          <?php echo $this->lists['filter']; ?>
          <input type="text" name="filter_search" id="filter_search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8');?>" class="inputbox form-control" onchange="document.adminForm.submit();" />
        </div>
        <div class="klevents-row klevents-justify-start klevents-nowrap">
          <button class="buttonfilter btn btn-primary" type="submit"><?php echo \Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
          <button class="buttonfilter btn btn-secondary" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo \Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
        <?php if ($this->settings->get('global_display',1)) : ?>
        <div class="klevents-row klevents-justify-start klevents-nowrap">
        <label for="limit"><?php echo \Text::_('com_planjeagenda_DISPLAY_NUM'); ?></label>&nbsp;
        <?php echo $this->events_pagination->getLimitBox(); ?>
        </div>
        <?php endif; ?>
            </div>
        <?php endif; ?>
  <?php endif; ?>

  <div class="klevents-sort klevents-sort-small">
    <div class="klevents-list-row klevents-small-list">
      <?php if (empty($this->print) && !empty($this->permissions->canPublishEvent)) : ?>
                <div class="sectiontableheader klevents-myevents-check">
          <input type="checkbox" value="" title="<?php echo \Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
        </div>
      <?php endif; ?>
      <div id="jem_date" class="sectiontableheader">&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_DATE', 'a.dates', $this->lists['order_Dir'], $this->lists['order']); ?></div>
      <?php if ($this->jemsettings->showtitle == 1) : ?>
        <div id="jem_title" class="sectiontableheader">&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_TITLE', 'a.title', $this->lists['order_Dir'], $this->lists['order']); ?></div>
      <?php endif; ?>
      <?php if ($this->jemsettings->showlocate == 1) : ?>
        <div id="jem_location" class="sectiontableheader">&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_LOCATION', 'l.venue', $this->lists['order_Dir'], $this->lists['order']); ?></div>
      <?php endif; ?>
      <?php if ($this->jemsettings->showcity == 1) : ?>
        <div id="jem_city" class="sectiontableheader">&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_CITY', 'l.city', $this->lists['order_Dir'], $this->lists['order']); ?></div>
      <?php endif; ?>
      <?php if ($this->jemsettings->showstate == 1) : ?>
        <div id="jem_state" class="sectiontableheader">&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_STATE', 'l.state', $this->lists['order_Dir'], $this->lists['order']); ?></div>
      <?php endif; ?>
      <?php if ($this->jemsettings->showcat == 1) : ?>
        <div id="jem_category" class="sectiontableheader">&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_CATEGORY', 'c.catname', $this->lists['order_Dir'], $this->lists['order']); ?></div>
      <?php endif; ?>
      <?php if ($this->jemsettings->showatte == 1) : ?>
                <div id="jem_atte" class="sectiontableheader">&nbsp;<?php echo \Text::_('com_planjeagenda_TABLE_ATTENDEES'); ?></div>
      <?php endif; ?>
      <div class="klevents-myevents-status" ><?php echo \Text::_('JSTATUS'); ?></div>
    </div>
  </div>

    <ul class="eventlist klevents-myevents">
        <?php if (count((array)$this->events) == 0) : ?>
            <li class="klevents-event"><?php echo \Text::_('com_planjeagenda_NO_EVENTS'); ?></li>
        <?php else : ?>
            <?php foreach ($this->events as $i => $row) : ?>
        <?php if (!empty($row->featured)) :   ?>
          <li class="klevents-event klevents-list-row klevents-small-list klevents-featured event-id<?php echo $row->id.$this->params->get('pageclass_sfx') . ' event_id' . $this->escape($row->id); ?>" itemscope="itemscope" itemtype="https://schema.org/Event">
                <?php else : ?>
          <li class="klevents-event klevents-list-row klevents-small-list klevents-odd<?php echo ($i % 2) . $this->params->get('pageclass_sfx') . ' event_id' . $this->escape($row->id); ?>" itemscope="itemscope" itemtype="https://schema.org/Event">
                <?php endif; ?>
            <?php /*<div><?php echo $this->events_pagination->getRowOffset( $i ); ?></div>*/ ?>

            <?php if (empty($this->print) && !empty($this->permissions->canPublishEvent)) : ?>
            <div class="klevents-event-info-small klevents-myevents-check" >
              <?php
              if (!empty($row->params) && $row->params->get('access-change', false)) :
                echo \HTMLHelper::_('grid.id', $i, $row->eventid) . '&nbsp;';
              endif;
              ?>
            </div>
            <?php endif; ?>

            <div class="klevents-event-info-small klevents-event-date" title="<?php echo \Text::_('com_planjeagenda_TABLE_DATE').': '.strip_tags(\PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times, $row->enddates, $row->endtimes, $this->jemsettings->showtime)); ?>">
              <i class="far fa-clock" aria-hidden="true"></i>
              <?php
                echo \PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times,
                  $row->enddates, $row->endtimes, $this->jemsettings->showtime);
              ?>
               <?php if ($this->jemsettings->showtitle == 0) : ?>
                <?php echo \PlanjeagendaOutput::recurrenceicon($row); ?>
                <?php echo \PlanjeagendaOutput::publishstateicon($row); ?>
                <?php if (!empty($row->featured)) :?>
                  <i class="klevents-featured-icon fa fa-exclamation-circle" aria-hidden="true"></i>
                <?php endif; ?>
               <?php endif; ?>
            </div>

            <?php if ($this->jemsettings->showtitle == 1) : ?>
              <div class="klevents-event-info-small klevents-event-title" title="<?php echo \Text::_('com_planjeagenda_TABLE_TITLE').': '.$this->escape($row->title); ?>">
                <a href="<?php echo \Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)); ?>"><?php echo $this->escape($row->title); ?></a>
                <?php echo \PlanjeagendaOutput::recurrenceicon($row) . \PlanjeagendaOutput::publishstateicon($row); ?>
                <?php if (!empty($row->featured)) :?>
                  <i class="klevents-featured-icon fa fa-exclamation-circle" aria-hidden="true"></i>
                <?php endif; ?>
              </div>
            <?php else : ?>
            <?php endif; ?>

            <?php if ($this->jemsettings->showlocate == 1) : ?>
              <?php if (!empty($row->venue)) : ?>
                <div class="klevents-event-info-small klevents-event-venue" title="<?php echo \Text::_('com_planjeagenda_TABLE_LOCATION').': '.$this->escape($row->venue); ?>">
                  <i class="fa fa-map-marker" aria-hidden="true"></i>
                  <?php if (($this->jemsettings->showlinkvenue == 1) && !empty($row->venueslug)) : ?>
                    <?php echo "<a href='".\Route::_(\PlanjeagendaHelperRoute::getVenueRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>"; ?>
                  <?php else : ?>
                    <?php echo $this->escape($row->venue); ?>
                  <?php endif; ?>
                </div>
              <?php else : ?>
                <div class="klevents-event-info-small klevents-event-venue">
                  <i class="fa fa-map-marker" aria-hidden="true"></i> -
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->jemsettings->showcity == 1) : ?>
              <?php if (!empty($row->city)) : ?>
                <div class="klevents-event-info-small klevents-event-city" title="<?php echo \Text::_('com_planjeagenda_TABLE_CITY').': '.$this->escape($row->city); ?>">
                  <i class="fa fa-building" aria-hidden="true"></i>
                  <?php echo $this->escape($row->city); ?>
                </div>
              <?php else : ?>
                <div class="klevents-event-info-small klevents-event-city"><i class="fa fa-building" aria-hidden="true"></i> -</div>
              <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->jemsettings->showstate == 1) : ?>
              <?php if (!empty($row->state)) : ?>
                <div class="klevents-event-info-small klevents-event-state" title="<?php echo \Text::_('com_planjeagenda_TABLE_STATE').': '.$this->escape($row->state); ?>">
                  <i class="fa fa-map" aria-hidden="true"></i>
                  <?php echo $this->escape($row->state); ?>
                </div>
              <?php else : ?>
                <div class="klevents-event-info-small klevents-event-state"><i class="fa fa-map" aria-hidden="true"></i> -</div>
              <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->jemsettings->showcat == 1) : ?>
              <div class="klevents-event-info-small klevents-event-category" title="<?php echo strip_tags(\Text::_('com_planjeagenda_TABLE_CATEGORY').': '.implode(", ", \PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist))); ?>">
                <i class="fa fa-tag" aria-hidden="true"></i>
                <?php echo implode(", ", \PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist)); ?>
              </div>
            <?php endif; ?>

                    <?php if ($this->jemsettings->showatte == 1) : ?>
                    <div class="klevents-event-info-small klevents-event-attendees" title="<?php echo \Text::_('com_planjeagenda_TABLE_ATTENDEES').': '.$this->escape($row->regCount); ?>">
            <i class="fa fa-user" aria-hidden="true"></i>
                        <?php
                        if ($this->jemsettings->showfroregistra || ($row->registra & 1)) {
                            $linkreg  = 'index.php?option=com_planjeagenda&amp;view=attendees&amp;id='.$row->id.'&Itemid='.$this->itemid;
                            $count = $row->regCount;
                            if ($row->maxplaces)
                            {
                                $count .= ' / '.$row->maxplaces;
                                if ($row->waitinglist && $row->waiting) {
                                    $count .= ' + '.$row->waiting;
                                }
                            }
                            if (!empty($row->unregCount)) {
                                $count .= ' - '.(int)$row->unregCount;
                            }
                            if (!empty($row->invited)) {
                                $count .= ', ? '.(int)$row->invited .' ';
                            }

                            if (!empty($row->regTotal) || empty($row->finished)) {
                            ?>
                            <a href="<?php echo $linkreg; ?>" title="<?php echo \Text::_('com_planjeagenda_MYEVENT_MANAGEATTENDEES'); ?>">
                                <?php echo $count; ?>
                            </a>
                            <?php
                            } else {
                                echo $count;
                            }
                        } else {
              echo \PlanjeagendaOutput::removebutton(NULL,NULL);
                        }
                        ?>
                    </div>
                    <?php endif; ?>

                    <div class="klevents-event-info-small klevents-myevents-status">
                        <?php // Ensure icon is not clickable if user isn't allowed to change state!
                        $enabled = empty($this->print) && !empty($row->params) && $row->params->get('access-change', false);
                        echo \HTMLHelper::_('jgrid.published', $row->published, $i, 'myevents.', $enabled);
                        ?>
                    </div>
                </li>

                <?php $i = 1 - $i; ?>
            <?php endforeach; ?>
        <?php endif; ?>
  </ul>

    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <input type="hidden" name="enableemailaddress" value="<?php echo $this->enableemailaddress; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="<?php echo $this->task; ?>" />
    <input type="hidden" name="option" value="com_planjeagenda" />
    <?php echo \HTMLHelper::_('form.token'); ?>
</form>



<div class="pagination">
    <?php echo $this->events_pagination->getPagesLinks(); ?>
</div>