<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

$uri = \Uri::getInstance();
if (empty($this->catrow->events)) { return; }
?>

<style>
 <?php
 $imagewidth = 'inherit';
 if ($this->jemsettings->imagewidth != 0) {
  $imagewidth = $this->jemsettings->imagewidth / 2;
  $imagewidth = $imagewidth.'px';
 }
 $imagewidthstring = 'klevents-imagewidth';
 if (\PlanjeagendaHelper::jemStringContains($this->params->get('pageclass_sfx'), $imagewidthstring)) {
   $pageclass_sfx = $this->params->get('pageclass_sfx');
   $imagewidthpos = strpos($pageclass_sfx, $imagewidthstring);
   $spacepos = strpos($pageclass_sfx, ' ', $imagewidthpos);
   if ($spacepos === false) {
     $spacepos = strlen($pageclass_sfx);
   }
   $startpos = $imagewidthpos + strlen($imagewidthstring);
   $endpos = $spacepos - $startpos;
   $imagewidth = substr($pageclass_sfx, $startpos, $endpos);
 }
 $imageheight = 'auto';
 $imageheigthstring = 'klevents-imageheight';
 if (\PlanjeagendaHelper::jemStringContains($this->params->get('pageclass_sfx'), $imageheigthstring)) {
   $pageclass_sfx = $this->params->get('pageclass_sfx');
   $imageheightpos = strpos($pageclass_sfx, $imageheigthstring);
   $spacepos = strpos($pageclass_sfx, ' ', $imageheightpos);
   if ($spacepos === false) {
     $spacepos = strlen($pageclass_sfx);
   }
   $startpos = $imageheightpos + strlen($imageheigthstring);
   $endpos = $spacepos - $startpos;
   $imageheight = substr($pageclass_sfx, $startpos, $endpos);
 }
 ?>

  #klevents .klevents-list-img {
    width: <?php echo $imagewidth; ?>;
  }

  #klevents .klevents-list-img img {
    width: <?php echo $imagewidth; ?>;
    height: <?php echo $imageheight; ?>;
  }

  @media not print {
    @media only all and (max-width: 47.938rem) {
      #klevents .klevents-list-img {
        width: 100%;
      }

      #klevents .klevents-list-img img {
        width: <?php echo $imagewidth; ?>;
        height: <?php echo $imageheight; ?>;
      }
    }
  }
</style>

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
          <li class="klevents-event klevents-row klevents-justify-start klevents-featured event_id<?php echo $row->id . $this->params->get('pageclass_sfx'); ?>" itemscope="itemscope" itemtype="https://schema.org/Event" <?php if ($this->jemsettings->showdetails == 1 && (!$isSafari) && ($this->jemsettings->gddisabled == 0)) : echo 'onclick="location.href=\''.\Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)).'\'"'; endif; ?>>
                <?php else : ?>
              <?php $odd = 0; ?>
          <li class="klevents-event klevents-row klevents-justify-start klevents-odd<?php echo ($odd +1) . ' event_id' . $row->id . $this->params->get('pageclass_sfx'); ?>" itemscope="itemscope" itemtype="https://schema.org/Event" <?php if ($this->jemsettings->showdetails == 1 && (!$isSafari) && ($this->jemsettings->gddisabled == 0)) : echo 'onclick="location.href=\''.\Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)).'\'"'; endif; ?>>
                <?php endif; ?>

          <?php if (($this->jemsettings->showeventimage == 1) && (!empty($row->datimage))): ?>
            <div headers="jem_eventimage" class="klevents-list-img" >
              <?php
              $dimage = \PlanjeagendaImage::flyercreator($row->datimage, 'event');
              echo \PlanjeagendaOutput::flyer($row, $dimage, 'event');
              ?>
            </div>
          <?php endif; ?>
          <div class="klevents-event-details" <?php if ($this->jemsettings->showdetails == 1 && (!$isSafari) && ($this->jemsettings->gddisabled == 1)) : echo 'onclick="location.href=\''.\Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)).'\'"'; endif; ?>>
            <?php if (($this->jemsettings->showtitle == 1) && ($this->jemsettings->showdetails == 1)) : // Display title as title of klevents-event with link ?>
            <h4 title="<?php echo \Text::_('com_planjeagenda_TABLE_TITLE').': '.$this->escape($row->title); ?>">
              <a href="<?php echo \Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)); ?>"><?php echo $this->escape($row->title); ?></a>
              <?php echo \PlanjeagendaOutput::recurrenceicon($row); ?>
              <?php echo \PlanjeagendaOutput::publishstateicon($row); ?>
              <?php if (!empty($row->featured)) :?>
                <i class="klevents-featured-icon fa fa-exclamation-circle" aria-hidden="true"></i>
              <?php endif; ?>
                            <?php echo $eventaccess; ?>
            </h4>

            <?php elseif (($this->jemsettings->showtitle == 1) && ($this->jemsettings->showdetails == 0)) : // Display title as title of klevents-event without link ?>
            <h4 title="<?php echo \Text::_('com_planjeagenda_TABLE_TITLE').': '.$this->escape($row->title); ?>">
              <?php echo $this->escape($row->title) . \PlanjeagendaOutput::recurrenceicon($row) . \PlanjeagendaOutput::publishstateicon($row); ?>
              <?php if (!empty($row->featured)) :?>
                <i class="klevents-featured-icon fa fa-exclamation-circle" aria-hidden="true"></i>
              <?php endif; ?>
                            <?php echo $eventaccess; ?>
            </h4>

            <?php elseif (($this->jemsettings->showtitle == 0) && ($this->jemsettings->showdetails == 1)) : // Display date as title of klevents-event with link ?>
            <h4>
              <a href="<?php echo \Route::_(\PlanjeagendaHelperRoute::getEventRoute($row->slug)); ?>">
              <?php
                echo \PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times,
                  $row->enddates, $row->endtimes, $this->jemsettings->showtime);
                echo \PlanjeagendaOutput::formatSchemaOrgDateTime($row->dates, $row->times,
                  $row->enddates, $row->endtimes);
              ?>
              </a>
              <?php echo \PlanjeagendaOutput::recurrenceicon($row); ?>
              <?php echo \PlanjeagendaOutput::publishstateicon($row); ?>
              <?php if (!empty($row->featured)) :?>
                <i class="klevents-featured-icon fa fa-exclamation-circle" aria-hidden="true"></i>
              <?php endif; ?>
                            <?php echo $eventaccess; ?>
            </h4>

            <?php else : // Display date as title of klevents-event without link ?>
            <h4>
              <?php
                echo \PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times,
                  $row->enddates, $row->endtimes, $this->jemsettings->showtime);
                echo \PlanjeagendaOutput::formatSchemaOrgDateTime($row->dates, $row->times,
                  $row->enddates, $row->endtimes);
              ?>
              <?php echo \PlanjeagendaOutput::recurrenceicon($row); ?>
              <?php echo \PlanjeagendaOutput::publishstateicon($row); ?>
              <?php if (!empty($row->featured)) :?>
                <i class="klevents-featured-icon fa fa-exclamation-circle" aria-hidden="true"></i>
              <?php endif; ?>
                            <?php echo $eventaccess; ?>
            </h4>
            <?php endif; ?>

                    <?php // Display other information below in a row
                    if ($row->user_has_access_event) :?>
            <div class="klevents-list-row">
              <?php if ($this->jemsettings->showtitle == 1) : ?>
                <div class="klevents-event-info" title="<?php echo \Text::_('com_planjeagenda_TABLE_DATE').': '.strip_tags(\PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times, $row->enddates, $row->endtimes, $this->jemsettings->showtime)); ?>" >
                  <i class="far fa-clock" aria-hidden="true"></i>
                  <?php
                    echo \PlanjeagendaOutput::formatShortDateTime($row->dates, $row->times,
                      $row->enddates, $row->endtimes, $this->jemsettings->showtime);
                    echo \PlanjeagendaOutput::formatSchemaOrgDateTime($row->dates, $row->times,
                      $row->enddates, $row->endtimes);
                  ?>
                </div>
              <?php endif; ?>

              <?php if ($this->jemsettings->showtitle == 0) : ?>
                <div class="klevents-event-info" title="<?php echo \Text::_('com_planjeagenda_TABLE_TITLE').': '.$this->escape($row->title); ?>">
                  <i class="fa fa-comment" aria-hidden="true"></i>
                  <?php echo $this->escape($row->title); ?>
                </div>
              <?php endif; ?>

              <?php if (($this->jemsettings->showlocate == 1) && (!empty($row->locid))) : ?>
                <div class="klevents-event-info" title="<?php echo \Text::_('com_planjeagenda_TABLE_LOCATION').': '.$this->escape($row->venue); ?>">
                  <i class="fa fa-map-marker" aria-hidden="true"></i>
                  <?php if ($this->jemsettings->showlinkvenue == 1) : ?>
                    <?php echo "<a href='".\Route::_(\PlanjeagendaHelperRoute::getVenueRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>"; ?>
                  <?php else : ?>
                    <?php echo $this->escape($row->venue); ?>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <?php if (($this->jemsettings->showcity == 1) && (!empty($row->city))) : ?>
                <div class="klevents-event-info" title="<?php echo \Text::_('com_planjeagenda_TABLE_CITY').': '.$this->escape($row->city); ?>">
                  <i class="fa fa-building" aria-hidden="true"></i>
                  <?php echo $this->escape($row->city); ?>
                </div>
              <?php endif; ?>

              <?php if (($this->jemsettings->showstate == 1) && (!empty($row->state))): ?>
                <div class="klevents-event-info" title="<?php echo \Text::_('com_planjeagenda_TABLE_STATE').': '.$this->escape($row->state); ?>">
                  <i class="fa fa-map" aria-hidden="true"></i>
                  <?php echo $this->escape($row->state); ?>
                </div>
              <?php endif; ?>

              <?php if ($this->jemsettings->showcat == 1) : ?>
                <div class="klevents-event-info" title="<?php echo strip_tags(\Text::_('com_planjeagenda_TABLE_CATEGORY').': '.implode(", ", \PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist))); ?>">
                  <i class="fa fa-tag" aria-hidden="true"></i>
                  <?php echo implode(", ", \PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist)); ?>
                </div>
              <?php endif; ?>

              <?php if (($this->jemsettings->showatte == 1) && (!empty($row->regCount))) : ?>
                <div class="klevents-event-info" title="<?php echo \Text::_('com_planjeagenda_TABLE_ATTENDEES').': '.$this->escape($row->regCount); ?>">
                  <i class="fa fa-user" aria-hidden="true"></i>
                  <?php echo $this->escape($row->regCount); ?>
                </div>
              <?php endif; ?>
            </div>
                    <?php endif; ?>
          </div>
                <?php if ($row->user_has_access_event) :?>
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
                <?php endif; ?>
        </li>
            <?php endforeach; ?>
  <?php endif; ?>
</ul>
<?php echo \PlanjeagendaOutput::lightbox();
