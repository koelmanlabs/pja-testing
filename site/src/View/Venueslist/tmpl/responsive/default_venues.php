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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$uri = \Uri::getInstance();
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
<style>

  .klevents-sort #jem_city,
  #klevents .klevents-event .klevents-event-city {
      flex: 1 <?php echo ($this->jemsettings->citywidth); ?>;
  }

  .klevents-sort #jem_state,
  #klevents .klevents-event .klevents-event-state {
    <?php if (($this->jemsettings->showstate == 1) && (!empty($this->jemsettings->statewidth))) : ?>
      flex: 1 <?php echo ($this->jemsettings->statewidth); ?>;
    <?php else : ?>
      flex: 1;
    <?php endif; ?>
  }

  .klevents-sort #jem_location,
  #klevents .klevents-event .klevents-event-venue {
      flex: 1 <?php echo ($this->jemsettings->locationwidth); ?>;
  }

</style>


<?php
function jem_common_show_filter(&$obj) {
  if ($obj->settings->get('global_show_filter',1) && !\PlanjeagendaHelper::jemStringContains($obj->params->get('pageclass_sfx'), 'klevents-hidefilter')) {
    return true;
  }
  if (\PlanjeagendaHelper::jemStringContains($obj->params->get('pageclass_sfx'), 'klevents-showfilter')) {
    return true;
  }
  return false;
}
?>
<?php if (jem_common_show_filter($this) && !\PlanjeagendaHelper::jemStringContains($this->params->get('pageclass_sfx'), 'klevents-filterbelow')): ?>
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
        <label for="limit"><?php echo \Text::_('com_planjeagenda_DISPLAY_NUM'); ?></label>
        <?php echo $this->pagination?->getLimitBox(); ?>
    </div>
    <?php endif; ?>
  </div>

<?php endif; ?>

<div class="klevents-sort klevents-sort-small">
    <div class="klevents-list-row klevents-small-list">
        <div id="jem_city" class="sectiontableheader"><i class="fa fa-building" aria-hidden="true"></i>&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_CITY', 'a.city', $this->lists['order_Dir'], $this->lists['order']); ?></div>

        <?php if ($this->params->get('showstate')) : ?>
        <div id="jem_state" class="sectiontableheader"><i class="fa fa-map" aria-hidden="true"></i>&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_STATE', 'a.state', $this->lists['order_Dir'], $this->lists['order']); ?></div>
        <?php endif; ?>

        <div id="jem_location" class="sectiontableheader"><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;<?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_TABLE_LOCATION', 'a.venue', $this->lists['order_Dir'], $this->lists['order']); ?></div>
    </div>
</div>

<ul class="eventlist">
  <?php if ($this->novenues == 1) : ?>
    <li class="klevents-event"><?php echo \Text::_('com_planjeagenda_NO_VENUES'); ?></li>
  <?php else : ?>
      <?php
      // Safari has problems with the "onclick" element in the <li>. It covers the links to location and category etc.
        // This detects the browser and just writes the onclick attribute if the browser is not Safari.
      $isSafari = false;
      if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') && !strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
        $isSafari = true;
      }
      ?>
            <?php $this->rows = $this->getRows(); ?>
            <?php foreach ($this->rows as $row) : ?>
            <?php
            // has user access
            $venueaccess = '';
            if (!$row->user_has_access_venue) {
                // show a closed lock icon
                $venueaccess = '<span class="icon-lock klevents-lockicon" aria-hidden="true"></span>';
            } ?>
                <?php if (!empty($row->featured)) :   ?>
                  <li class="klevents-event klevents-list-row klevents-small-list klevents-featured event-id<?php echo $row->id.$this->params->get('pageclass_sfx') . ' venue_id' . $this->escape($row->id); ?>" itemscope="itemscope" itemtype="https://schema.org/Event"  >
                <?php else : ?>
                    <li class="klevents-event klevents-list-row klevents-small-list klevents-odd<?php echo ($row->odd +1) . $this->params->get('pageclass_sfx') . ' venue_id' . $this->escape($row->id); ?>" itemscope="itemscope" itemtype="https://schema.org/Event"  >
                <?php endif; ?>

                <?php if (!empty($row->city)) : ?>
                  <div class="klevents-event-info-small klevents-event-city venue-big" title="<?php echo \Text::_('com_planjeagenda_TABLE_CITY').': '.$this->escape($row->city); ?>">
                    <?php echo $this->escape($row->city); ?>
                  </div>
                <?php else : ?>
                  <div class="klevents-event-info-small klevents-event-city">-</div>
                <?php endif; ?>

                <?php if ($this->params->get('showstate')) : ?>
                    <?php if (!empty($row->state)) : ?>
                    <div class="klevents-event-info-small klevents-event-state" title="<?php echo \Text::_('com_planjeagenda_TABLE_STATE').': '.$this->escape($row->state); ?>">
                        <?php echo $this->escape($row->state); ?>
                    </div>
                    <?php else : ?>
                    <div class="klevents-event-info-small klevents-event-state">-</div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($row->locid)) : ?>
                  <div class="klevents-event-info-small klevents-event-venue" title="<?php echo \Text::_('com_planjeagenda_TABLE_LOCATION').': '.$this->escape($row->venue); ?>">
                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                          <?php
                        if ($this->jemsettings->showlinkvenue == 1) :
                            echo $row->id != 0 ? "<a href='".\Route::_(\PlanjeagendaHelperRoute::getVenueRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>" : '-';
                        else :
                            echo $row->id ? $this->escape($row->venue) : '-';
                         endif; ?>
                        <?php echo \PlanjeagendaOutput::publishstateicon($row); ?>
                    <?php echo $venueaccess;?>
                    </div>
                <?php else : ?>
                  <div class="klevents-event-info-small klevents-event-venue">
                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                          <?php
                        if ($this->jemsettings->showlinkvenue == 1) :
                            echo $row->id != 0 ? "<a href='".\Route::_(\PlanjeagendaHelperRoute::getVenueRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>" : '-';
                        else :
                            echo $row->id ? $this->escape($row->venue) : '-';
                         endif; ?>
                        <?php echo \PlanjeagendaOutput::publishstateicon($row); ?>
                    <?php echo $venueaccess;?>
                  </div>
                <?php endif; ?>

                <meta itemprop="name" content="<?php echo $this->escape($row->venue); ?>" />
                <meta itemprop="url" content="<?php echo rtrim($uri->base(), '/').\Route::_(\PlanjeagendaHelperRoute::getVenueRoute($row->venueslug ?? $row->id)); ?>" />
                <meta itemprop="identifier" content="<?php echo rtrim($uri->base(), '/').\Route::_(\PlanjeagendaHelperRoute::getVenueRoute($row->venueslug ?? $row->id)); ?>" />
                <div itemtype="https://schema.org/Place" itemscope itemprop="location" style="display: none;" >
                <?php if (!empty($row->locid)) : ?>
                    <meta itemprop="name" content="<?php echo $this->escape($row->venue); ?>" />
                <?php else : ?>
                    <meta itemprop="name" content="None" />
                <?php endif;
                
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

<div class="pagination">
    <?php echo $this->pagination?->getPagesLinks(); ?>
</div>