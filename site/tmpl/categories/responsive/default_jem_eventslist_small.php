<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$uri = \Uri::getInstance();
if (empty($this->catrow->events)) { return; }
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
      flex: 1 <?php echo ($this->jemsettings->datewidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
  }

  .klevents-sort #jem_title,
  #klevents .klevents-event .klevents-event-title {
    <?php if (($this->jemsettings->showtitle == 1) && (!empty($this->jemsettings->titlewidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->titlewidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
  }

  .klevents-sort #jem_location,
  #klevents .klevents-event .klevents-event-venue {
    <?php if (($this->jemsettings->showlocate == 1) && (!empty($this->jemsettings->locationwidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->locationwidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
  }

  .klevents-sort #jem_city,
  #klevents .klevents-event .klevents-event-city {
    <?php if (($this->jemsettings->showcity == 1) && (!empty($this->jemsettings->citywidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->citywidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
  }

  .klevents-sort #jem_state,
  #klevents .klevents-event .klevents-event-state {
    <?php if (($this->jemsettings->showstate == 1) && (!empty($this->jemsettings->statewidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->statewidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
  }

  .klevents-sort #jem_category,
  #klevents .klevents-event .klevents-event-category {
    <?php if (($this->jemsettings->showcat == 1) && (!empty($this->jemsettings->catfrowidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->catfrowidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
  }

  .klevents-sort #jem_atte,
  #klevents .klevents-event .klevents-event-attendees {
    <?php if (($this->jemsettings->showatte == 1) && (!empty($this->jemsettings->attewidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->attewidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
  }
</style>

<div class="klevents-sort klevents-sort-small">
  <div class="klevents-list-row klevents-small-list">
    <div id="jem_date" class="sectiontableheader"><i class="far fa-clock" aria-hidden="true"></i>&nbsp;<?php echo \Text::_('com_planjeagenda_TABLE_DATE'); ?></div>
    <?php if ($this->jemsettings->showtitle == 1) : ?>
      <div id="jem_title" class="sectiontableheader"><i class="fa fa-comment" aria-hidden="true"></i>&nbsp;<?php echo \Text::_('com_planjeagenda_TABLE_TITLE'); ?></div>
    <?php endif; ?>
    <?php if ($this->jemsettings->showlocate == 1) : ?>
      <div id="jem_location" class="sectiontableheader"><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;<?php echo \Text::_('com_planjeagenda_TABLE_LOCATION'); ?></div>
    <?php endif; ?>
    <?php if ($this->jemsettings->showcity == 1) : ?>
      <div id="jem_city" class="sectiontableheader"><i class="fa fa-building" aria-hidden="true"></i>&nbsp;<?php echo \Text::_('com_planjeagenda_TABLE_CITY'); ?></div>
    <?php endif; ?>
    <?php if ($this->jemsettings->showstate == 1) : ?>
      <div id="jem_state" class="sectiontableheader"><i class="fa fa-map" aria-hidden="true"></i>&nbsp;<?php echo \Text::_('com_planjeagenda_TABLE_STATE'); ?></div>
    <?php endif; ?>
    <?php if ($this->jemsettings->showcat == 1) : ?>
      <div id="jem_category" class="sectiontableheader"><i class="fa fa-tag" aria-hidden="true"></i>&nbsp;<?php echo \Text::_('com_planjeagenda_TABLE_CATEGORY'); ?></div>
    <?php endif; ?>
  </div>
</div>

<ul class="eventlist">
  <?php if (empty($this->catrow->events)) : ?>
    <li class="klevents-event"><?php echo \Text::_('com_planjeagenda_NO_EVENTS'); ?></li>
  <?php else : ?>
    <?php
    // Safari has problems with the "onclick" element in the <li>. It covers the links to location and category etc.
    // This detects the browser and just writes the onclick attribute if the broswer is not Safari.
    $isSafari = false;
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') && !strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
      $isSafari = true;
    }
    ?>
    <?php foreach ($this->catrow->events as $row) : ?>
            <?php
            // has user access
            $eventaccess = '';
            if (!$row->user_has_access_event) {
                // show a closed lock icon
                $eventaccess = '<span class="icon-lock klevents-lockicon" aria-hidden="true"></span>';
            } ?>
      <?php if (!empty($row->featured)) :   ?>
        <li class="klevents-event klevents-list-row klevents-small-list klevents-featured event-id<?php echo $row->id.$this->params->get('pageclass_sfx'); ?>" itemscope="itemscope" itemtype="https://schema.org/Event">
      <?php else : ?>
        <li class="klevents-event klevents-list-row klevents-small-list klevents-odd<?php echo ($row->odd +1) . $this->params->get('pageclass_sfx'); ?>" itemscope="itemscope" itemtype="https://schema.org/Event">
      <?php endif; ?>

            <div class="klevents-event-info-small klevents-event-date" title="<?php echo \Text::_('com_planjeagenda_TABLE_DATE').': '.strip_tags(\PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times, $row->enddates, $row->endtimes, $this->jemsettings->showtime)); ?>" <?php if ($this->jemsettings->showdetails == 1 && (!$isSafari)) : echo 'onclick="location.href=\''.\Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)).'\'"';
            endif; ?>>
              <i class="far fa-clock" aria-hidden="true"></i>
              <?php
                echo \PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times,
                  $row->enddates, $row->endtimes, $this->jemsettings->showtime);
                echo \PlanjeagendaOutput::formatSchemaOrgDateTime($row->dates, $row->times,
                  $row->enddates, $row->endtimes);
              ?>
               <?php if ($this->jemsettings->showtitle == 0) : ?>
                <?php echo \PlanjeagendaOutput::recurrenceicon($row); ?>
                <?php echo \PlanjeagendaOutput::publishstateicon($row); ?>
                <?php if (!empty($row->featured)) :?>
                  <i class="klevents-featured-icon fa fa-exclamation-circle" aria-hidden="true"></i>
                <?php endif; ?>
                    <?php echo $eventaccess; ?>
               <?php endif; ?>
            </div>

            <?php if ($this->jemsettings->showtitle == 1) : ?>
              <div class="klevents-event-info-small klevents-event-title" title="<?php echo \Text::_('com_planjeagenda_TABLE_TITLE').': '.$this->escape($row->title); ?>">
                <i class="fa fa-comment" aria-hidden="true"></i>
                <a href="<?php echo \Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)); ?>"><?php echo $this->escape($row->title); ?></a>
                <?php echo \PlanjeagendaOutput::recurrenceicon($row) . \PlanjeagendaOutput::publishstateicon($row); ?>
                <?php if (!empty($row->featured)) :?>
                  <i class="klevents-featured-icon fa fa-exclamation-circle" aria-hidden="true"></i>
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <?php if (!$row->user_has_access_venue) : ?>
            <?php if ($this->jemsettings->showlocate == 1) : ?>
              <?php if (!empty($row->locid)) : ?>
                <div class="klevents-event-info-small klevents-event-venue" title="<?php echo \Text::_('com_planjeagenda_TABLE_LOCATION').': '.$this->escape($row->venue); ?>">
                  <i class="fa fa-map-marker" aria-hidden="true"></i>
                  <?php if ($this->jemsettings->showlinkvenue == 1) : ?>
                    <?php echo "<a href='".\Route::_(\PlanjeagendaHelperRoute::getVenueRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>"; ?>
                  <?php else : ?>
                    <?php echo $this->escape($row->venue); ?>
                  <?php endif; ?>
                </div>
              <?php else : ?>
                <div class="klevents-event-info-small klevents-event-venue"><i class="fa fa-map-marker" aria-hidden="true"></i> -</div>
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
            <?php endif; ?>
            <?php if ($this->jemsettings->showcat == 1) : ?>
              <div class="klevents-event-info-small klevents-event-category" title="<?php echo strip_tags(\Text::_('com_planjeagenda_TABLE_CATEGORY').': '.implode(", ", \PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist))); ?>">
                <i class="fa fa-tag" aria-hidden="true"></i>
                <?php echo implode(", ", \PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist)); ?>
              </div>
            <?php endif; ?>

            <meta itemprop="name" content="<?php echo $this->escape($row->title); ?>" />
            <meta itemprop="url" content="<?php echo rtrim($uri->base(), '/').\Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)); ?>" />
            <meta itemprop="identifier" content="<?php echo rtrim($uri->base(), '/').\Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)); ?>" />
            <div itemtype="https://schema.org/Place" itemscope itemprop="location" style="display: none;" >
              <?php if (!empty($row->locid)) : ?>
                <meta itemprop="name" content="<?php echo $this->escape($row->venue); ?>" />
              <?php else : ?>
                <meta itemprop="name" content="None" />
              <?php endif; ?>
              <?php
              $microadress = '';
              if (!empty($row->city)) {
                $microadress .= $this->escape($row->city);
              }
              if (!empty($microadress)) {
                $microadress .= ', ';
              }
              if (!empty($row->state)) {
                $microadress .= $this->escape($row->state);
              }
              if (empty($microadress)) {
                $microadress .= '-';
              }
              ?>
              <meta itemprop="address" content="<?php echo $microadress; ?>" />
            </div>

      </li>
    <?php endforeach; ?>
  <?php endif; ?>
</ul>
