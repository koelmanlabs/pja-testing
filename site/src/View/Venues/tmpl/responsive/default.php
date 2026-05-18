<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div id="klevents" class="jem_venues<?php echo $this->pageclass_sfx; ?>">

    <div class="pja-ev-actionbar" style="margin-bottom:1.25rem;">
        <div class="pja-ev-actionbar__left">
            <h1 class="pja-page-title" style="margin:0;"><?php echo $this->escape($this->params->get('page_heading', Text::_('com_planjeagenda_VENUES'))); ?></h1>
        </div>
        <div class="pja-ev-actionbar__right">
            <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=eventslist&format=raw&layout=ics'); ?>"
               class="pja-ev-action-icon" title="iCal">
                <svg width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/></svg>
                <span>iCal</span>
            </a>
        </div>
    </div>

    <?php if (empty($this->rows)): ?>
    <div style="text-align:center;padding:3rem;color:#9ca3af;border:1px dashed #d1d5db;border-radius:14px;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:.75rem;opacity:.4;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        <p>Geen locaties gevonden.</p>
    </div>
    <?php else: ?>
    <?php foreach ($this->rows as $row): ?>
    <div class="pja-venue-card" itemscope itemtype="https://schema.org/Place">
        <div class="pja-venue-icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
        </div>
        <div style="flex:1;min-width:0;">
            <a href="<?php echo $row->linkEventsPublished; ?>" class="pja-venue-card__name" itemprop="url">
                <span itemprop="name"><?php echo $this->escape($row->venue); ?></span>
                <?php echo PlanjeagendaOutput::publishstateicon($row); ?>
                <?php if (!$row->user_has_access_venue): ?>
                <svg width="11" height="11" viewBox="0 0 448 512" fill="#9ca3af" aria-label="Beperkte toegang"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
                <?php endif; ?>
            </a>
            <?php if ($row->user_has_access_venue): ?>
            <div class="pja-venue-card__addr" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                <?php if ($row->city): ?><span itemprop="addressLocality"><?php echo $this->escape($row->city); ?></span><?php endif; ?>
                <?php if ($row->city && $row->state): ?>, <?php endif; ?>
                <?php if ($row->state): ?><span itemprop="addressRegion"><?php echo $this->escape($row->state); ?></span><?php endif; ?>
                <?php if ($row->country): ?><span itemprop="addressCountry"><?php echo $this->escape($row->country); ?></span><?php endif; ?>
            </div>
            <div class="pja-venue-card__footer">
                <a href="<?php echo $row->linkEventsPublished; ?>" class="pja-card__cta">
                    Bekijk locatie
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="pagination"><?php echo $this->pagination?->getPagesLinks(); ?></div>
</div>
<?php echo PlanjeagendaOutput::lightbox(); ?>
