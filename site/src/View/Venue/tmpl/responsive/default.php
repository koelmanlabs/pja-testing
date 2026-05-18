<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$v       = $this->venue;
$s       = $this->settings;
$params  = $this->params;
$user    = Factory::getApplication()->getIdentity();
$evUrl   = Route::_('index.php?option=com_planjeagenda&view=eventslist');
$mapserv = (int)$s->get('global_show_mapserv', 0);

// Colour accent
$venueColor = '#2e7d32';
?>
<div id="klevents" class="jem_venue<?php echo $this->pageclass_sfx; ?>"
     itemscope itemtype="https://schema.org/Place">
<meta itemprop="name" content="<?php echo $this->escape($v->title); ?>">

    <!-- Back link -->
    <a href="<?php echo $evUrl; ?>" class="pja-back-link">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Terug naar activiteiten
    </a>

    <!-- Action bar -->
    <div class="pja-ev-actionbar" style="margin-bottom:1.25rem;">
        <div class="pja-ev-actionbar__left"></div>
        <div class="pja-ev-actionbar__right">
            <?php if (!empty($this->print_link)): ?>
            <a href="<?php echo $this->print_link; ?>" class="pja-ev-action-icon"
               title="Afdrukken" onclick="window.print();return false;">
                <svg width="14" height="14" viewBox="0 0 512 512" fill="currentColor"><path d="M128 0C92.7 0 64 28.7 64 64v96h64V64H354.7L384 93.3V160h64V93.3c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0H128zM384 352v32 64H128V384 368 352H384zm64 32h32c17.7 0 32-14.3 32-32V256c0-35.3-28.7-64-64-64H64c-35.3 0-64 28.7-64 64v96c0 17.7 14.3 32 32 32H64v64c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V384z"/></svg>
            </a>
            <?php endif; ?>
            <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=eventslist&format=raw&layout=ics'); ?>"
               class="pja-ev-action-icon" title="iCal">
                <svg width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/></svg>
                <span>iCal</span>
            </a>
        </div>
    </div>

    <!-- Hero image -->
    <?php echo PlanjeagendaOutput::flyer($v, $this->limage, 'venue'); ?>

    <!-- Accent + Title -->
    <div class="pja-accent-bar" style="background:<?php echo $venueColor; ?>"></div>
    <h1 class="pja-page-title"><?php echo $this->escape($v->title); ?></h1>

    <!-- Two-column layout -->
    <div class="pja-detail-layout">

        <!-- Body -->
        <div class="pja-detail-body">

            <!-- Description -->
            <?php if ($s->get('global_show_locdescription', 1) && !empty($this->venuedescription) && $this->venuedescription !== '<br>'): ?>
            <div style="font-size:.96rem;color:#374151;line-height:1.75;margin-bottom:1.5rem;" itemprop="description">
                <?php echo $this->venuedescription; ?>
            </div>
            <?php endif; ?>

            <!-- Custom fields -->
            <?php
            $customs = [];
            for ($i = 1; $i <= 10; $i++) {
                $val = $v->{'custom' . $i} ?? '';
                if ($val) {
                    if (preg_match('%^https?://%', $val)) $val = '<a href="' . $this->escape($val) . '" target="_blank" rel="noopener">' . $this->escape($val) . '</a>';
                    $customs[$i] = $val;
                }
            }
            if ($customs): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem;margin-bottom:1.5rem;">
                <?php foreach ($customs as $i => $val): ?>
                <div style="background:#f8fafd;border:1px solid #e0e8f0;border-radius:8px;padding:.75rem 1rem;">
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:.2rem;"><?php echo Text::_('com_planjeagenda_VENUE_CUSTOM_FIELD' . $i); ?></div>
                    <div style="font-size:.9rem;color:#1a2e5a;font-weight:600;"><?php echo $val; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Map -->
            <?php if (in_array($mapserv, [2, 3, 5])): ?>
            <h2 class="pja-section-heading">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
                Kaart
            </h2>
            <?php if ($mapserv === 3): ?>
            <input type="hidden" id="latitude"   value="<?php echo (float)$v->latitude; ?>">
            <input type="hidden" id="longitude"  value="<?php echo (float)$v->longitude; ?>">
            <input type="hidden" id="venue"      value="<?php echo $this->escape($v->venue ?? $v->title); ?>">
            <input type="hidden" id="street"     value="<?php echo $this->escape($v->street ?? ''); ?>">
            <input type="hidden" id="city"       value="<?php echo $this->escape($v->city ?? ''); ?>">
            <input type="hidden" id="state"      value="<?php echo $this->escape($v->state ?? ''); ?>">
            <input type="hidden" id="postalCode" value="<?php echo $this->escape($v->postalCode ?? ''); ?>">
            <?php endif; ?>
            <div class="klevents-map"><?php echo PlanjeagendaOutput::mapicon($v, null, $s); ?></div>
            <?php endif; ?>

            <!-- Events at this venue -->
            <?php if ($s->get('global_show_listevents', 1)): ?>
            <h2 class="pja-section-heading">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                Activiteiten
            </h2>
            <form action="<?php echo htmlspecialchars($this->action); ?>" method="post" id="adminForm">
                <div class="pja-event-table-wrap">
                    <?php echo $this->loadTemplate('events_table'); ?>
                </div>
                <input type="hidden" name="option" value="com_planjeagenda">
                <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>">
                <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>">
                <input type="hidden" name="view" value="venue">
                <input type="hidden" name="id" value="<?php echo $v->id; ?>">
            </form>
            <div class="pagination"><?php echo $this->pagination?->getPagesLinks(); ?></div>
            <?php endif; ?>

            <!-- Attachments -->
            <?php if (!empty($v->attachments)):
                $this->attachments = $v->attachments;
                echo $this->loadTemplate('attachments');
            endif; ?>

        </div>

        <!-- Info aside -->
        <aside class="pja-info-panel">
            <div class="pja-info-panel__heading">Locatie-info</div>

            <?php if ($s->get('global_show_detailsadress', 1)): ?>

            <?php if (!empty($v->url)): ?>
            <div class="pja-info-row">
                <div class="pja-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></div>
                <div><span class="pja-info-label">Website</span>
                <span class="pja-info-value"><a href="<?php echo $v->url; ?>" target="_blank" rel="noopener"><?php echo $this->escape($v->urlclean ?? $v->url); ?></a></span></div>
            </div>
            <?php endif; ?>

            <?php if ($v->street || $v->city || $v->postalCode || $v->state || $v->country): ?>
            <div class="pja-info-row">
                <div class="pja-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></div>
                <div><span class="pja-info-label">Adres</span>
                <span class="pja-info-value pja-venue-address"
                      itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                    <?php if ($v->street): ?><span itemprop="streetAddress"><?php echo $this->escape($v->street); ?></span><br><?php endif; ?>
                    <?php if ($v->postalCode || $v->city): ?>
                        <span itemprop="postalCode"><?php echo $this->escape($v->postalCode); ?></span>
                        <span itemprop="addressLocality"><?php echo $this->escape($v->city); ?></span><br>
                    <?php endif; ?>
                    <?php if ($v->state): ?><span itemprop="addressRegion"><?php echo $this->escape($v->state); ?></span><br><?php endif; ?>
                    <?php if ($v->country): ?>
                        <?php echo $v->countryimg ?: $this->escape($v->country); ?>
                        <meta itemprop="addressCountry" content="<?php echo $this->escape($v->country); ?>">
                    <?php endif; ?>
                </span></div>
            </div>
            <?php endif; ?>

            <?php if ($v->latitude && $v->longitude): ?>
            <a href="https://maps.google.com/?q=<?php echo (float)$v->latitude; ?>,<?php echo (float)$v->longitude; ?>"
               target="_blank" rel="noopener" class="pja-venue-map-link">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
                Bekijk op Google Maps
            </a>
            <?php endif; ?>

            <?php if (in_array($mapserv, [1, 4])): ?>
            <div class="klevents-map" style="margin-top:1rem;">
                <?php echo PlanjeagendaOutput::mapicon($v, null, $s); ?>
            </div>
            <?php endif; ?>

            <?php endif; // show_detailsadress ?>

            <?php if ($this->permissions->canEditVenue || $this->permissions->canAddVenue): ?>
            <div class="pja-btn-stack" style="margin-top:1.25rem;">
                <?php echo PlanjeagendaOutput::editbutton($v, $params, null, $this->permissions->canEditVenue, 'venue'); ?>
                <?php echo PlanjeagendaOutput::copybutton($v, $params, null, $this->permissions->canAddVenue, 'venue'); ?>
            </div>
            <?php endif; ?>
        </aside>

    </div><!-- /.pja-detail-layout -->
</div>
<?php echo PlanjeagendaOutput::lightbox(); ?>
