<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    GNU/GPL v3
 *
 * Event detail view — styled to match the Planjeagenda design system.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\OutputHelper;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\RouteHelper;

$params      = $this->item->params;
$attribs     = json_decode($this->item->attribs ?? '{}');
$user        = Factory::getApplication()->getIdentity();
$jemsettings = PlanjeagendaHelper::config();
$app         = Factory::getApplication();
$doc         = $app->getDocument();
$uri         = Uri::getInstance();

// Expire meta for old-event archiving
if ($jemsettings->oldevent > 0) {
    $enddate = strtotime($this->item->enddates ?: ($this->item->dates ?: date('Y-m-d')));
    $doc->addCustomTag('<meta http-equiv="expires" content="' . date('D, d M Y H:i:s', strtotime('+1 day', $enddate)) . '"/>');
}

// Build category classes for JS/CSS hooks
$catclasses = '';
foreach ((array)$this->categories as $cat) {
    $catclasses .= ' cat_id' . (int)$cat->id;
}

// Category colour for the accent bar
$catColor = '#2e7d32'; // default green
foreach ((array)$this->categories as $cat) {
    if (!empty($cat->color)) {
        $c = ltrim($cat->color, '#');
        $catColor = '#' . $c;
        break;
    }
}

// Event image
$eventImg = '';
if (!empty($this->item->datimage)) {
    $img = $this->item->datimage;
    if (is_string($img) && !str_starts_with($img, '{')) {
        $eventImg = $img;
    } elseif (is_string($img)) {
        $d = json_decode($img, true);
        $eventImg = $d['image'] ?? $d['imagefile'] ?? $d['src'] ?? '';
    }
}

// Format date helper
$fmtDate = function(string $d, string $t = '', string $de = '', string $te = ''): string {
    if (!$d) return '';
    $locale = 'nl_NL';
    $start = new \DateTime($d . ($t && $t !== '00:00:00' ? ' ' . $t : ''));
    $out = $start->format('l j F Y');
    if ($t && $t !== '00:00:00') {
        $out .= ' · ' . $start->format('H:i');
        if ($te && $te !== '00:00:00') {
            $out .= ' – ' . (new \DateTime(($de ?: $d) . ' ' . $te))->format('H:i');
        }
    }
    if ($de && $de !== $d) {
        $end = new \DateTime($de);
        $out .= ' t/m ' . $end->format('l j F Y');
    }
    return $out;
};

$dateStr = $fmtDate(
    $this->item->dates ?? '',
    $this->item->times ?? '',
    $this->item->enddates ?? '',
    $this->item->endtimes ?? ''
);

// Event URL for sharing
$eventUrl = rtrim($uri->base(), '/') . Route::_(RouteHelper::getEventRoute($this->item->slug));

if ($params->get('access-view')) :
?>
<div id="klevents" class="pja-event event_id<?php echo (int)$this->item->did . $catclasses; ?> jem_event<?php echo $this->escape($this->pageclass_sfx ?? ''); ?>"
     itemscope itemtype="https://schema.org/Event">

    <meta itemprop="url" content="<?php echo $eventUrl; ?>">
    <meta itemprop="name" content="<?php echo $this->escape($this->item->title); ?>">

<style>
/* ── Event detail page ──────────────────────────────────────────────── */
.pja-event { font-family: var(--pja-font, 'Plus Jakarta Sans', system-ui, sans-serif); }

/* Hero image */
.pja-event__hero {
    position: relative;
    width: 100%; height: 340px;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 2rem;
    background: #f0f4f8;
}
.pja-event__hero img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.pja-event__hero-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 4rem;
}

/* Accent strip */
.pja-event__accent {
    height: 4px;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    background: var(--pja-green, #2e7d32);
}

/* Category pill */
.pja-event__cat-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .25rem .85rem;
    border-radius: 20px;
    font-size: .78rem; font-weight: 700;
    color: #fff;
    margin-bottom: 1rem;
}

/* Title */
.pja-event__title {
    font-size: clamp(1.5rem, 3.5vw, 2.2rem);
    font-weight: 800;
    color: #1a2e5a;
    line-height: 1.2;
    letter-spacing: -.03em;
    margin: 0 0 1.5rem;
}

/* Two-column layout: main + aside */
.pja-event__layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 2.5rem;
    align-items: start;
}

/* Info card (aside) */
.pja-event__info-card {
    background: #f8fafd;
    border: 1px solid #e0e8f0;
    border-radius: 14px;
    padding: 1.5rem;
    position: sticky;
    top: 84px;
}
.pja-event__info-card h3 {
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #9ca3af;
    margin: 0 0 1rem;
}

/* Info rows */
.pja-event__info-row {
    display: flex;
    gap: .75rem;
    margin-bottom: 1rem;
    align-items: flex-start;
    font-size: .88rem;
    color: #374151;
    line-height: 1.5;
}
.pja-event__info-row:last-child { margin-bottom: 0; }
.pja-event__info-icon {
    flex-shrink: 0;
    width: 36px; height: 36px;
    border-radius: 8px;
    background: #fff;
    border: 1px solid #e0e8f0;
    display: flex; align-items: center; justify-content: center;
    color: #2e7d32;
}
.pja-event__info-label {
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: #9ca3af; display: block; margin-bottom: .1rem;
}
.pja-event__info-value { font-weight: 600; color: #1a2e5a; }
.pja-event__info-value a { color: #1565c0; text-decoration: none; }
.pja-event__info-value a:hover { text-decoration: underline; }

/* CTA buttons in info card */
.pja-event__actions { display: flex; flex-direction: column; gap: .5rem; margin-top: 1.25rem; }
.pja-event__btn-primary {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    padding: .65rem 1rem;
    background: #2e7d32; color: #fff;
    border: none; border-radius: 10px;
    font-size: .88rem; font-weight: 700; font-family: inherit;
    text-decoration: none; cursor: pointer; transition: background .15s;
}
.pja-event__btn-primary:hover { background: #4caf50; text-decoration: none; color: #fff; }
.pja-event__btn-secondary {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    padding: .55rem 1rem;
    background: #fff; color: #374151;
    border: 1.5px solid #e0e8f0;
    border-radius: 10px;
    font-size: .85rem; font-weight: 600; font-family: inherit;
    text-decoration: none; cursor: pointer; transition: all .15s;
}
.pja-event__btn-secondary:hover { border-color: #2e7d32; color: #2e7d32; text-decoration: none; background: #f0f9f0; }

/* Share row */
.pja-event__share {
    display: flex; gap: .5rem; margin-top: 1rem; align-items: center;
    font-size: .78rem; color: #9ca3af;
}
.pja-event__share-btn {
    width: 32px; height: 32px;
    border-radius: 50%; border: 1px solid #e0e8f0;
    background: #fff; color: #6b7280;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all .15s; cursor: pointer;
}
.pja-event__share-btn:hover { border-color: #1a2e5a; color: #1a2e5a; }

/* Description body */
.pja-event__body { min-width: 0; }
.pja-event__description {
    font-size: .97rem;
    line-height: 1.75;
    color: #374151;
}
.pja-event__description h1, .pja-event__description h2,
.pja-event__description h3 { color: #1a2e5a; margin-top: 1.5rem; }
.pja-event__description p { margin-bottom: 1rem; }
.pja-event__description a { color: #1565c0; }
.pja-event__description img { border-radius: 8px; max-width: 100%; }

/* Section heading */
.pja-event__section-title {
    font-size: 1rem; font-weight: 800;
    color: #1a2e5a; letter-spacing: -.02em;
    margin: 2rem 0 1rem;
    display: flex; align-items: center; gap: .5rem;
}
.pja-event__section-title::after {
    content: ''; flex: 1; height: 1px; background: #e0e8f0;
}

/* Venue card */
.pja-event__venue-card {
    background: #f8fafd; border: 1px solid #e0e8f0;
    border-radius: 12px; padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.pja-event__venue-name {
    font-size: 1rem; font-weight: 700; color: #1a2e5a; margin-bottom: .5rem;
}
.pja-event__venue-address { font-size: .88rem; color: #6b7280; line-height: 1.6; }
.pja-event__venue-map-link {
    display: inline-flex; align-items: center; gap: .35rem;
    margin-top: .75rem; font-size: .83rem; font-weight: 600;
    color: #1565c0; text-decoration: none;
}
.pja-event__venue-map-link:hover { text-decoration: underline; }

/* Contact card */
.pja-event__contact-card {
    background: #fff; border: 1px solid #e0e8f0;
    border-radius: 12px; padding: 1.25rem;
    display: flex; gap: 1rem; align-items: flex-start;
    margin-bottom: 1.5rem;
}
.pja-event__contact-avatar {
    width: 44px; height: 44px; flex-shrink: 0;
    background: #e8f5e9; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #2e7d32;
}

/* Custom fields */
.pja-event__custom {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: .75rem; margin-bottom: 1.5rem;
}
.pja-event__custom-item {
    background: #f8fafd; border: 1px solid #e0e8f0;
    border-radius: 8px; padding: .75rem 1rem;
}
.pja-event__custom-label {
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: #9ca3af; margin-bottom: .2rem;
}
.pja-event__custom-value { font-size: .9rem; color: #1a2e5a; font-weight: 600; }

/* Registration */
.pja-event__registration {
    background: linear-gradient(135deg, #e8f5e9, #f0f9f0);
    border: 1px solid #c8e6c9; border-radius: 12px;
    padding: 1.5rem; margin-bottom: 1.5rem;
}
.pja-event__registration h3 { color: #1b5e20; margin-bottom: 1rem; }

/* Back link */
.pja-event__back {
    display: inline-flex; align-items: center; gap: .4rem;
    color: #6b7280; text-decoration: none;
    font-size: .83rem; font-weight: 600;
    margin-bottom: 1.25rem; transition: color .15s;
}
.pja-event__back:hover { color: #1a2e5a; text-decoration: none; }

/* ── PRINT styles ──────────────────────────────────────────────── */
@media print {
    .pja-ev-actionbar, .pja-event__actions,
    .pja-event__share, .pja-event__btn-primary,
    .pja-event__btn-secondary { display: none !important; }
    
    .pja-event__layout { grid-template-columns: 1fr; }
    .pja-event__info-card {
        position: static;
        background: none; border: none; padding: 0;
        margin-bottom: 1.5rem;
    }
    .pja-event__info-card h3 { color: #000; }
    .pja-event__info-row { break-inside: avoid; }
    .pja-event__hero { height: 200px; }
    .pja-event__title { font-size: 1.6rem; }
    .pja-event__accent { background: #000; }
    
    /* Print header */
    .pja-event::before {
        content: 'Planjeagenda.nl';
        display: block;
        font-size: .8rem;
        color: #666;
        margin-bottom: 1rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid #ccc;
    }
    
    body { color: #000 !important; }
    a { color: #000 !important; text-decoration: none !important; }
    a[href]::after { content: ' (' attr(href) ')'; font-size: .75rem; color: #666; }
    a[href^="#"]::after, a[href^="javascript"]::after { content: ''; }
}

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .pja-event__layout { grid-template-columns: 1fr; }
    .pja-event__info-card { position: static; order: -1; }
    .pja-event__hero { height: 220px; }
    .pja-event__custom { grid-template-columns: 1fr; }
    .pja-event__actions { flex-direction: row; flex-wrap: wrap; }
    .pja-event__btn-primary, .pja-event__btn-secondary { flex: 1; }
}
</style>

    <!-- ── Back link ─────────────────────────────────────────────── -->
    <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=eventslist'); ?>"
       class="pja-event__back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Terug naar activiteiten
    </a>

    <!-- ── Action bar (print/ical/mailto) ──────────────────────── -->
    <div class="pja-ev-actionbar" style="margin-bottom:1.25rem;">
        <div class="pja-ev-actionbar__left">
            <?php if (!empty($this->item->catname)): ?>
            <span class="pja-ev-action-link" style="cursor:default;font-weight:700;">
                <?php echo $this->escape($this->item->catname); ?>
            </span>
            <?php endif; ?>
        </div>
        <div class="pja-ev-actionbar__right">
        
                    <?php
            echo Text::_('com_planjeagenda_EVENT') . OutputHelper::recurrenceicon($this->item) .' ';
            if($this->item_root) {
                echo OutputHelper::editbutton($this->item_root, $params, $attribs, $this->permissions->canEditEvent, 'editevent') . ' ';
            }
            if(!$this->item_root || ($this->item_root && $this->item->recurrence_first_id)) {
                echo OutputHelper::editbutton($this->item, $params, $attribs, $this->permissions->canEditEvent, 'editevent') . ' ';
            }
            echo OutputHelper::copybutton($this->item, $params, $attribs, $this->permissions->canAddEvent, 'editevent');
            ?>
        
        
            <?php if (!empty($this->print_link)): ?>
            <a href="<?php echo $this->print_link; ?>"
               class="pja-ev-action-icon" title="<?php echo Text::_('JGLOBAL_PRINT'); ?>"
               onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480');return false;" aria-label="Afdrukken">
                <svg width="14" height="14" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M128 0C92.7 0 64 28.7 64 64v96h64V64H354.7L384 93.3V160h64V93.3c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0H128zM384 352v32 64H128V384 368 352H384zm64 32h32c17.7 0 32-14.3 32-32V256c0-35.3-28.7-64-64-64H64c-35.3 0-64 28.7-64 64v96c0 17.7 14.3 32 32 32H64v64c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V384z"/></svg>
            </a>
            <?php endif; ?>
            <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=event&format=raw&id=' . $this->item->slug); ?>"
               class="pja-ev-action-icon" title="iCal downloaden">
                <svg width="14" height="14" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/></svg>
                <span>iCal</span>
            </a>
        </div>
    </div>

    <!-- ── Hero image ────────────────────────────────────────────── -->
    <?php if ($eventImg): ?>
    <div class="pja-event__hero">
        <img src="<?php echo $this->escape($eventImg); ?>"
             alt="<?php echo $this->escape($this->item->title); ?>"
             itemprop="image">
    </div>
    <?php endif; ?>

    <!-- ── Accent bar ─────────────────────────────────────────────── -->
    <div class="pja-event__accent" style="background:<?php echo $catColor; ?>"></div>

    <!-- ── Category pill ─────────────────────────────────────────── -->
    <?php if (!empty($this->item->catname)): ?>
    <span class="pja-event__cat-pill" style="background:<?php echo $catColor; ?>">
        <?php echo $this->escape($this->item->catname); ?>
    </span>
    <?php endif; ?>

    <!-- ── Title ─────────────────────────────────────────────────── -->
    <h1 class="pja-event__title" itemprop="name">
        <?php echo $this->escape($this->item->title); ?>
    </h1>

    <!-- ── Two-column layout ─────────────────────────────────────── -->
    <div class="pja-event__layout">

        <!-- MAIN: description + venue + contact + registration -->
        <div class="pja-event__body">

            <!-- Description -->
            <?php
            $hasDesc = $params->get('event_show_description', '1')
                && ($this->item->fulltext || $this->item->introtext);
            if ($hasDesc && $params->get('access-view')):
                $bodyText = (!$params->get('event_show_intro') && $this->item->fulltext)
                    ? $this->item->fulltext
                    : ($this->item->text ?? $this->item->introtext ?? '');
            ?>
            <div class="pja-event__description" itemprop="description">
                <?php echo $bodyText; ?>
            </div>
            <?php endif; ?>

            <!-- Custom fields -->
            <?php
            $customFields = [];
            for ($i = 1; $i <= 10; $i++) {
                $val = $this->item->{'custom' . $i} ?? '';
                if ($val) {
                    if (preg_match('%^https?://%', $val)) {
                        $val = '<a href="' . $this->escape($val) . '" target="_blank" rel="noopener">' . $this->escape($val) . '</a>';
                    }
                    $customFields[$i] = $val;
                }
            }
            if ($customFields): ?>
            <div class="pja-event__custom">
                <?php foreach ($customFields as $i => $val): ?>
                <div class="pja-event__custom-item">
                    <div class="pja-event__custom-label"><?php echo Text::_('com_planjeagenda_EVENT_CUSTOM_FIELD' . $i); ?></div>
                    <div class="pja-event__custom-value"><?php echo $val; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Venue -->
            <?php if (!empty($this->item->locid) && !empty($this->item->venue) && $params->get('event_show_venue', '1')): ?>
            <h2 class="pja-event__section-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
                Locatie
            </h2>
            <div class="pja-event__venue-card"
                 itemprop="location" itemscope itemtype="https://schema.org/Place">
                <meta itemprop="name" content="<?php echo $this->escape($this->item->venue); ?>">

                <?php if ($this->item->user_has_access_venue): ?>
                    <div class="pja-event__venue-name">
                        <?php if ($params->get('event_show_detlinkvenue') == 1 && !empty($this->item->url)): ?>
                            <a href="<?php echo $this->item->url; ?>" target="_blank" rel="noopener">
                                <?php echo $this->escape($this->item->venue); ?>
                            </a>
                        <?php elseif ($params->get('event_show_detlinkvenue') == 2 && !empty($this->item->venueslug)): ?>
                            <a href="<?php echo Route::_(\PlanjeagendaHelperRoute::getVenueRoute($this->item->venueslug)); ?>">
                                <?php echo $this->escape($this->item->venue); ?>
                            </a>
                        <?php else: ?>
                            <?php echo $this->escape($this->item->venue); ?>
                        <?php endif; ?>
                    </div>
                    <div class="pja-event__venue-address"
                         itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                        <?php if ($this->item->street): ?>
                            <span itemprop="streetAddress"><?php echo $this->escape($this->item->street); ?></span><br>
                        <?php endif; ?>
                        <?php if ($this->item->postalCode || $this->item->city): ?>
                            <span itemprop="postalCode"><?php echo $this->escape($this->item->postalCode); ?></span>
                            <span itemprop="addressLocality"><?php echo $this->escape($this->item->city); ?></span><br>
                        <?php endif; ?>
                        <?php if ($this->item->state): ?>
                            <span itemprop="addressRegion"><?php echo $this->escape($this->item->state); ?></span><br>
                        <?php endif; ?>
                        <?php if ($this->item->country): ?>
                            <span itemprop="addressCountry"><?php echo $this->escape($this->item->country); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($this->item->latitude && $this->item->longitude): ?>
                    <a href="https://maps.google.com/?q=<?php echo (float)$this->item->latitude; ?>,<?php echo (float)$this->item->longitude; ?>"
                       target="_blank" rel="noopener" class="pja-event__venue-map-link">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        Bekijk op kaart
                    </a>
                    <?php endif; ?>

                    <!-- Map embed if configured -->
                    <?php $mapserv = $params->get('event_show_mapserv');
                    if ($mapserv == 2 || $mapserv == 3 || $mapserv == 5): ?>
                    <div class="klevents-map" style="margin-top:1rem;">
                        <?php echo \PlanjeagendaOutput::mapicon($this->item, 'event', $params); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Venue description -->
                    <?php if ($params->get('event_show_locdescription', '1') && !empty($this->item->locdescription)
                        && $this->item->locdescription !== '<br>'): ?>
                    <div style="margin-top:.75rem;font-size:.88rem;color:#374151;line-height:1.6;border-top:1px solid #e0e8f0;padding-top:.75rem;">
                        <?php echo $this->item->locdescription; ?>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="pja-event__venue-name">
                        <?php echo $this->escape($this->item->venue); ?>
                        <?php if ($this->item->city): ?>
                            <span style="font-weight:400;color:#6b7280"> · <?php echo $this->escape($this->item->city); ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:.82rem;color:#9ca3af;margin-top:.35rem;">
                        <svg width="11" height="11" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true" style="vertical-align:middle;">
                            <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/>
                        </svg>
                        Adresgegevens niet beschikbaar
                    </div>
                <?php endif; ?>
            </div>

            <!-- Venue attachments -->
            <?php if (!empty($this->item->vattachments)): ?>
                <?php $this->attachments = $this->item->vattachments; ?>
                <?php echo $this->loadTemplate('attachments'); ?>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Contact -->
            <?php if ($params->get('event_show_contact') && !empty($this->item->conid)): ?>
            <h2 class="pja-event__section-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                Contact
            </h2>
            <div class="pja-event__contact-card">
                <div class="pja-event__contact-avatar" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <div>
                    <?php
                    $contact = $this->item->conname;
                    if ($params->get('event_link_contact') && !empty($this->item->conid)):
                        $needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->conid . '&catid=' . $this->item->concatid;
                        $menuObj = $app->getMenu();
                        $cItem = $menuObj->getItems('link', $needle, true);
                        $cLink = !empty($cItem) ? $needle . '&Itemid=' . $cItem->id : $needle;
                    ?>
                    <div style="font-weight:700;color:#1a2e5a;">
                        <a href="<?php echo Route::_($cLink); ?>" style="color:#1565c0;"><?php echo $this->escape($contact); ?></a>
                    </div>
                    <?php else: ?>
                    <div style="font-weight:700;color:#1a2e5a;"><?php echo $this->escape($contact); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($this->item->contelephone)): ?>
                    <div style="font-size:.85rem;color:#6b7280;margin-top:.25rem;">
                        <a href="tel:<?php echo $this->escape($this->item->contelephone); ?>"
                           style="color:inherit;text-decoration:none;">
                            <?php echo $this->escape($this->item->contelephone); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Attachments -->
            <?php if (!empty($this->item->attachments)): ?>
                <?php $this->attachments = $this->item->attachments; ?>
                <?php echo $this->loadTemplate('attachments'); ?>
            <?php endif; ?>

            <!-- Registration -->
            <?php if ($this->showAttendees && $params->get('event_show_registration', '0')): ?>
            <div class="pja-event__registration">
                <h3>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:middle;margin-right:.3rem;">
                        <path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/>
                    </svg>
                    Aanmelden
                </h3>
                <dl class="klevents-dl">
                    <?php echo $this->loadTemplate('attendees'); ?>
                </dl>
            </div>
            <?php endif; ?>

            <!-- Plugin output -->
            <?php if (!empty($this->item->pluginevent->onEventEnd)): ?>
            <div style="margin-top:1.5rem;">
                <?php echo $this->item->pluginevent->onEventEnd; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- ASIDE: info card -->
        <aside class="pja-event__info-card" aria-label="Activiteitinformatie">
            <h3>Over deze activiteit</h3>

            <!-- Date/time -->
            <?php if ($dateStr): ?>
            <div class="pja-event__info-row">
                <div class="pja-event__info-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 448 512" fill="currentColor">
                        <path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192z"/>
                    </svg>
                </div>
                <div>
                    <span class="pja-event__info-label">Datum &amp; tijd</span>
                    <span class="pja-event__info-value">
                        <?php
                        echo OutputHelper::formatLongDateTime(
                            $this->item->dates, $this->item->times,
                            $this->item->enddates, $this->item->endtimes
                        );
                        echo OutputHelper::formatSchemaOrgDateTime(
                            $this->item->dates, $this->item->times,
                            $this->item->enddates, $this->item->endtimes
                        );
                        ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Venue (summary) -->
            <?php if (!empty($this->item->venue) && $params->get('event_show_venue_name') != 0): ?>
            <div class="pja-event__info-row">
                <div class="pja-event__info-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </div>
                <div>
                    <span class="pja-event__info-label">Locatie</span>
                    <span class="pja-event__info-value">
                        <?php echo $this->escape($this->item->venue);
                        if ($this->item->city) echo '<br><span style="font-weight:400;color:#6b7280;font-size:.83rem;">' . $this->escape($this->item->city) . '</span>';
                        ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Categories -->
            <?php if ($params->get('event_show_category') != 0 && !empty($this->categories)): ?>
            <div class="pja-event__info-row">
                <div class="pja-event__info-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 448 512" fill="currentColor">
                        <path d="M0 80V229.5c0 17 6.7 33.3 18.7 45.3l176 176c25 25 65.5 25 90.5 0L418.7 317.3c25-25 25-65.5 0-90.5l-176-176c-12-12-28.3-18.7-45.3-18.7H48C21.5 32 0 53.5 0 80zm112 32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
                    </svg>
                </div>
                <div>
                    <span class="pja-event__info-label"><?php echo count((array)$this->categories) > 1 ? 'Categorieën' : 'Categorie'; ?></span>
                    <span class="pja-event__info-value">
                        <?php foreach ((array)$this->categories as $i => $cat):
                            if ($i > 0) echo ', ';
                            if ($params->get('event_link_category') == 1):
                                echo '<a href="' . Route::_(RouteHelper::getCategoryRoute($cat->catslug)) . '">' . $this->escape($cat->catname) . '</a>';
                            else:
                                echo $this->escape($cat->catname);
                            endif;
                        endforeach; ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Author -->
            <?php if ($params->get('event_show_author') && !empty($this->item->author)): ?>
            <div class="pja-event__info-row">
                <div class="pja-event__info-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <div>
                    <span class="pja-event__info-label">Geplaatst door</span>
                    <span class="pja-event__info-value">
                        <?php $author = $this->item->created_by_alias ?: $this->item->author;
                        echo $this->escape($author); ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Hits -->
            <?php if ($params->get('event_show_hits') && !empty($this->item->hits)): ?>
            <div class="pja-event__info-row">
                <div class="pja-event__info-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                </div>
                <div>
                    <span class="pja-event__info-label">Bekeken</span>
                    <span class="pja-event__info-value"><?php echo (int)$this->item->hits; ?> keer</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action buttons -->
            <div class="pja-event__actions">
                <?php if ($this->showAttendees && $params->get('event_show_registration', '0')): ?>
                <a href="#registration" class="pja-event__btn-primary" id="registration">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/>
                    </svg>
                    Aanmelden
                </a>
                <?php endif; ?>

                <a href="https://www.google.com/calendar/render?action=TEMPLATE&text=<?php echo urlencode($this->item->title); ?>&dates=<?php echo str_replace('-','', $this->item->dates ?? date('Y-m-d')); ?>/<?php echo str_replace('-','', ($this->item->enddates ?: $this->item->dates ?? date('Y-m-d'))); ?>&location=<?php echo urlencode($this->item->venue ?? ''); ?>"
                   target="_blank" rel="noopener" class="pja-event__btn-secondary">
                    <svg width="13" height="13" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192z"/></svg>
                    Google Agenda
                </a>

                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&format=raw&id=' . $this->item->slug); ?>"
                   class="pja-event__btn-secondary">
                    <svg width="13" height="13" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/></svg>
                    Download iCal
                </a>

                <?php if ($this->permissions->canEditEvent): ?>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&task=event.edit&a_id=' . (int)$this->item->id.'&return='.base64_encode($uri)); ?>"
                   class="pja-event__btn-secondary">
                    <svg width="13" height="13" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1v32c0 8.8 7.2 16 16 16h32zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z"/></svg>
                    Bewerken
                </a>
                <?php endif; ?>
            </div>

            <!-- Share buttons -->
            <div class="pja-event__share">
                <span>Delen:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($eventUrl); ?>"
                   target="_blank" rel="noopener" class="pja-event__share-btn" aria-label="Delen op Facebook">
                    <svg width="12" height="12" viewBox="0 0 320 512" fill="currentColor"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg>
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($this->item->title); ?>&url=<?php echo urlencode($eventUrl); ?>"
                   target="_blank" rel="noopener" class="pja-event__share-btn" aria-label="Delen op X">
                    <svg width="12" height="12" viewBox="0 0 512 512" fill="currentColor"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                </a>
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($this->item->title . ' ' . $eventUrl); ?>"
                   target="_blank" rel="noopener" class="pja-event__share-btn" aria-label="Delen via WhatsApp">
                    <svg width="12" height="12" viewBox="0 0 448 512" fill="currentColor"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                </a>
                <button onclick="navigator.clipboard?.writeText('<?php echo $eventUrl; ?>');this.title='Gekopieerd!'"
                        class="pja-event__share-btn" aria-label="Link kopiëren" title="Link kopiëren">
                    <svg width="12" height="12" viewBox="0 0 512 512" fill="currentColor"><path d="M307 34.8c-11.5 5.1-19 16.6-19 29.2v64H176C78.8 128 0 206.8 0 304C0 417.3 81.5 467.9 100.2 478.1c2.5 1.4 5.3 1.9 8.1 1.9c10.9 0 19.7-8.9 19.7-19.7c0-7.5-4.3-14.4-9.8-19.5C108.8 431.9 96 414.4 96 384c0-53 43-96 96-96h96v64c0 12.6 7.4 24.1 19 29.2s25 3 34.4-5.4l160-144c6.7-6.1 10.6-14.7 10.6-23.8s-3.8-17.7-10.6-23.8l-160-144c-9.4-8.5-22.9-10.6-34.4-5.4z"/></svg>
                </button>
            </div>
        </aside>

    </div><!-- /.pja-event__layout -->

</div><!-- /.pja-event -->

<?php endif; /* access-view */ ?>

<?php echo OutputHelper::lightbox(); ?>
