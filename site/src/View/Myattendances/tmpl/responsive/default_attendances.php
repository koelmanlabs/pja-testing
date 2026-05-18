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

<style>
  <?php if (!empty($this->jemsettings->tablewidth)) : ?>
    #klevents #adminForm {
      width: <?php echo ($this->jemsettings->tablewidth); ?>;
    }
  <?php endif; ?>

  .klevents-sort #jem_date,
  #klevents .klevents-event .klevents-event-date {
    <?php if (!empty($this->jemsettings->datewidth)) : ?>
      flex: 1 <?php echo intval(($this->jemsettings->datewidth))-5 . '%'; /*take a little off to fit comment*/?>;
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
      flex: 1 <?php echo intval(($this->jemsettings->titlewidth))-5 . '%'; /*take a little off to fit comment*/?>;
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
      flex: 1 <?php echo intval(($this->jemsettings->locationwidth))-3 . '%'; /*take a little off to fit comment*/?>;
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

    .klevents-sort #jem_places,
    #klevents .klevents-event .klevents-myattendances-places {
        flex: 0 5%;
        text-align: center;
    }

  .klevents-sort #jem_status,
  #klevents .klevents-event .klevents-myattendances-status {
        flex: 0 5%;
        text-align: center;
  }
    .klevents-sort #jem_comment,
  #klevents .klevents-event .klevents-myattendances-comments {
    flex: 0 5%;
  }
</style>

<script>
    function tableOrdering(order, dir, view)
    {
        var form = document.getElementById("adminForm");

        form.filter_order.value     = order;
        form.filter_order_Dir.value = dir;
        form.submit(view);
    }
</script>

<h2><?php echo \Text::_('com_planjeagenda_REGISTERED_TO'); ?></h2>

<form action="<?php echo htmlspecialchars($this->action); ?>" method="post" id="adminForm" name="adminForm">
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
          <button class="btn btn-primary" type="submit"><?php echo \Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
          <button class="btn btn-secondary" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo \Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
        <?php if ($this->settings->get('global_display',1)) : ?>
  <div class="klevents-row klevents-justify-start klevents-nowrap">
    <label for="limit"><?php echo \Text::_('com_planjeagenda_DISPLAY_NUM'); ?></label>&nbsp;
      <?php echo $this->attending_pagination->getLimitBox(); ?>
  </div>
<?php endif; ?>
            </div>
        <?php endif; ?>
  <?php endif; ?>

    <div class="klevents-sort klevents-sort-small">
    <div class="klevents-list-row klevents-small-list">
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
      <div id="jem_places" class="sectiontableheader"><?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_PLACES', 'r.places', $this->lists['order_Dir'], $this->lists['order']); ?></div>
      <div id="jem_status" class="sectiontableheader"><?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_HEADER_WAITINGLIST_STATUS', 'r.status', $this->lists['order_Dir'], $this->lists['order']); ?></div>
      <?php if (!empty($this->jemsettings->regallowcomments)) : ?>
        <div id="jem_comment" class="sectiontableheader"><?php echo \Text::_('com_planjeagenda_COMMENT'); ?></div>
      <?php endif; ?>
    </div>
  </div>

    <ul class="eventlist klevents-myattendees">
        <?php if (count((array)$this->attending) == 0) : ?>
            <li class="klevents-event"><?php echo \Text::_('com_planjeagenda_NO_EVENTS'); ?></li>
        <?php else : ?>
            <?php foreach ($this->attending as $i => $row) : ?>
        <?php if (!empty($row->featured)) :   ?>
          <li class="klevents-event klevents-list-row klevents-small-list klevents-featured event-id<?php echo $row->id.$this->params->get('pageclass_sfx'). ' event_id' . $this->escape($row->id); ?>" itemscope="itemscope" itemtype="https://schema.org/Event">
                <?php else : ?>
          <li class="klevents-event klevents-list-row klevents-small-list klevents-odd<?php echo ($i % 2) . $this->params->get('pageclass_sfx'). ' event_id' . $this->escape($row->id); ?>" itemscope="itemscope" itemtype="https://schema.org/Event">
                <?php endif; ?>

                    <div class="klevents-event-info-small klevents-event-date" title="<?php echo \Text::_('com_planjeagenda_TABLE_DATE').': '.strip_tags(\PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times, $row->enddates, $row->endtimes, $this->jemsettings->showtime)); ?>">
            <i class="far fa-clock" aria-hidden="true"></i>
            <?php
              echo \PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times,
                $row->enddates, $row->endtimes, $this->jemsettings->showtime);
            ?>
             <?php if ($this->jemsettings->showtitle == 0) : ?>
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

            <div class="klevents-event-info-small klevents-myattendances-places" title="<?php echo \Text::_('com_planjeagenda_TABLE_PLACES').': '.$this->escape($row->places); ?>">
                <?php echo $this->escape($row->places); ?>
            </div>

                    <div class="klevents-event-info-small klevents-myattendances-status">
                        <?php
                        $status = (int)$row->status;
                        if ($status === 1 && $row->waiting == 1) { $status = 2; }
                        echo jemhtml::toggleAttendanceStatus($row->id, $status, false, $this->print);
                        ?>
                    </div>

                    <?php if (!empty($this->jemsettings->regallowcomments)) : ?>
            <div class="klevents-event-info-small klevents-myattendances-comments">
              <?php
              $len  = ($this->print) ? 256 : 16;
              $cmnt = (\Joomla\String\StringHelper::strlen($row->comment) > $len) ? (\Joomla\String\StringHelper::substr($row->comment, 0, $len - 2).'&hellip;') : $row->comment;
              if (!empty($cmnt)) :
                echo ($this->print) ? $cmnt : \HTMLHelper::_('tooltip', $row->comment, null, null, $cmnt, null, null);
              endif;
              ?>
            </div>
                    <?php endif; ?>
                <?php $i = 1 - $i; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="<?php echo $this->task; ?>" />
    <input type="hidden" name="option" value="com_planjeagenda" />
</form>


<div class="pagination">
    <?php echo $this->attending_pagination->getPagesLinks(); ?>
</div>
