<?php
/**
 * Calendar view — FullCalendar 6 (month / week / day / list)
 * Fully styled to match the Planjeagenda design system.
 * FullCalendar loaded from jsDelivr CDN with graceful offline fallback.
 */
defined('_JEXEC') or die;

$app = \Joomla\CMS\Factory::getApplication();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$icalUrl   = Route::_('index.php?option=com_planjeagenda&view=eventslist&format=raw&layout=ics');
$jsonUrl   = Route::_('index.php?option=com_planjeagenda&view=calendar&format=json');
$evlistUrl = Route::_('index.php?option=com_planjeagenda&view=eventslist');
$addUrl    = Route::_('index.php?option=com_planjeagenda&view=editevent&a_id=0');
$user      = Factory::getApplication()->getIdentity();
$canAdd    = $user->authorise('core.create', 'com_planjeagenda');

// Allow pre-selecting view via URL param: ?calview=week|day|list|month
$initView  = 'dayGridMonth';
$paramView = Factory::getApplication()->input->getString('calview', '');
$viewMap   = [
    'month' => 'dayGridMonth',
    'week'  => 'timeGridWeek',
    'day'   => 'timeGridDay',
    'list'  => 'listMonth',
];
if (isset($viewMap[$paramView])) {
    $initView = $viewMap[$paramView];
}

// Haal de dynamische parameter op voor de fallback/huisstijlkleur
$primaryColor = $this->params->get('eventbackgroundcolor', '#2e7d32');

// Joomla language for FullCalendar locale
$langTag = strtolower(substr(Factory::getApplication()->getLanguage()->getTag(), 0, 2));

// Zorg voor een flexibele fallback: pak catIds, en als die leeg is, probeer categories
$categoriesList = !empty($this->catIds) ? $this->catIds : (!empty($this->categories) ? $this->categories : []);
?>

<div class="pja-container mt-4"></div>

<div id="pja-calendar-wrap">

<div class="pja-filter-dashboard mb-4 p-3 bg-light rounded shadow-sm">
    <div class="row g-3 align-items-center">
        <div class="col-md-4">
            <label for="pja-municipality-select" class="form-label small text-muted fw-bold text-uppercase">Gemeente / Regio</label>
            <select id="pja-municipality-select" class="form-select form-select-sm">
                <option value="all">🔄 Alle gemeenten</option>
                <?php foreach ($this->municipalities as $muni) : ?>
                    <option value="<?php echo (int) $muni->id; ?>"><?php echo htmlspecialchars($muni->title, ENT_QUOTES); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-8">
            <label class="form-label small text-muted fw-bold text-uppercase d-block">Categorie</label>
            <div class="d-flex flex-wrap gap-2">
                </div>
        </div>
    </div>
</div>




    <div class="pja-calendar-filters mb-4 p-3 bg-light rounded shadow-sm">
        <small class="text-muted d-block mb-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
            <?php echo Text::_('COM_PLANJEAGENDA_FILTER_BY_CATEGORY'); ?>
        </small>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" class="btn btn-sm btn-dark pja-filter-btn active" data-cat-id="all">
                🔄 <?php echo Text::_('COM_PLANJEAGENDA_ALL_CATEGORIES'); ?>
            </button>

            <?php if (!empty($categoriesList)) : ?>
                <?php foreach ($categoriesList as $category) : 
                    // Bepaal de eigenschappen dynamisch (vangt zowel objecten als arrays op)
                    $catObj   = (object) $category;
                    $color    = !empty($catObj->color) ? $catObj->color : '#2e7d32';
                    $catTitle = !empty($catObj->title) ? $catObj->title : (!empty($catObj->catname) ? $catObj->catname : 'Categorie');
                    $catId    = !empty($catObj->id) ? $catObj->id : (!empty($catObj->catid) ? $catObj->catid : 0);
                    ?>
                    <button type="button" 
                            class="btn btn-sm text-white pja-filter-btn" 
                            data-cat-id="<?php echo (int) $catId; ?>"
                            style="background-color: <?php echo $color; ?>; border-color: <?php echo $color; ?>; --pja-orig-color: <?php echo $color; ?>;">
                        <?php echo htmlspecialchars($catTitle, ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="pja-ev-actionbar" style="margin-bottom:1rem;">
        <div class="pja-ev-actionbar__left">
            <div id="pja-cal-viewbar" role="group" aria-label="Kalenderweergave"></div>
        </div>
        <div class="pja-ev-actionbar__right">
            <a href="<?php echo $evlistUrl; ?>" class="pja-ev-action-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                Activiteitenlijst
            </a>
            <a href="<?php echo $icalUrl; ?>" class="pja-ev-action-icon" title="Download iCal">
                <svg width="15" height="15" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true">
                    <path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/>
                </svg>
                <span>iCal</span>
            </a>
            <?php if ($canAdd): ?>
            <a href="<?php echo $addUrl; ?>" class="pja-ev-action-add">
                <svg width="13" height="13" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true">
                    <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/>
                </svg>
                Activiteit plaatsen
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="pja-cal-loading" style="display:none;text-align:center;padding:3rem;color:#6b7280;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2">
            <circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="32">
                <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
            </circle>
        </svg>
        <p style="margin-top:.5rem;font-size:.85rem;">Kalender laden...</p>
    </div>

    <div id="pja-fullcalendar"></div>

    <div id="pja-cal-fallback" style="display:none;padding:2rem;text-align:center;border:1px solid #e0e8f0;border-radius:12px;background:#f8fafd;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin:0 auto 1rem">
            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
        </svg>
        <p style="font-weight:700;color:#1a2e5a;margin-bottom:.5rem;">Kalender niet beschikbaar</p>
        <p style="font-size:.85rem;color:#6b7280;margin-bottom:1rem;">
            FullCalendar kon niet geladen worden (geen internetverbinding).
        </p>
        <a href="<?php echo $evlistUrl; ?>" class="pja-ev-action-add" style="display:inline-flex;">
            Bekijk activiteitenlijst
        </a>
    </div>

    <div id="pja-cal-popup" aria-modal="true" role="dialog" aria-label="Activiteitdetails" hidden>
        <div class="pja-cal-popup__color"></div>
        <div class="pja-cal-popup__body">
            <button class="pja-cal-popup__close" onclick="pjaClosePopup()" aria-label="Sluiten">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
            <a class="pja-cal-popup__title" href="#"></a>
            <div class="pja-cal-popup__meta pja-cal-popup__date"></div>
            <div class="pja-cal-popup__meta pja-cal-popup__venue"></div>
            <div class="pja-cal-popup__meta pja-cal-popup__cat"></div>
            <p class="pja-cal-popup__intro"></p>
            <a class="pja-cal-popup__cta" href="#">Bekijk activiteit →</a>
        </div>
    </div>
    <div id="pja-cal-popup-overlay" hidden onclick="pjaClosePopup()"></div>

</div>

<?php
$wa       = $app->getDocument()->getWebAssetManager();
$hasLocal = $wa->assetExists('script', 'pja.fullcalendar');
if ($hasLocal) {
    $wa->useStyle('pja.fullcalendar');
    $wa->useScript('pja.fullcalendar');
    if ($wa->assetExists('script', 'pja.fullcalendar.nl')) {
        $wa->useScript('pja.fullcalendar.nl');
    }
}
?>
<?php if (!$hasLocal): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.css">
<script>
var pjaFCTimeout = setTimeout(function() {
    if (typeof FullCalendar === 'undefined') {
        document.getElementById('pja-cal-fallback').style.display = 'block';
        document.getElementById('pja-cal-loading').style.display  = 'none';
    }
}, 6000);
</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js" onerror="clearTimeout(pjaFCTimeout);document.getElementById('pja-cal-fallback').style.display='block';document.getElementById('pja-cal-loading').style.display='none';"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.14/locales/nl.global.min.js"></script>
<?php else: ?>
<script>if(typeof pjaFCTimeout!=='undefined')clearTimeout(pjaFCTimeout);</script>
<?php endif; ?>

<style>
/* ── Wrapper ─────────────────────────────────────────────────────── */
#pja-calendar-wrap { font-family: var(--pja-font, 'Plus Jakarta Sans', system-ui, sans-serif); }

/* ── Filter bar styling ──────────────────────────────────────────── */
.pja-filter-btn { transition: all 0.2s ease-in-out; opacity: 0.5; }
.pja-filter-btn.active { opacity: 1 !important; box-shadow: 0 2px 4px rgba(0,0,0,0.15); font-weight: bold; }
.pja-calendar-filters:not(.filtering-active) .pja-filter-btn { opacity: 1; }

/* ── View switcher pills ─────────────────────────────────────────── */
#pja-cal-viewbar { display: flex; gap: .3rem; flex-wrap: wrap; }
.pja-cal-view-btn {
    padding: .3rem .85rem;
    border: 1.5px solid var(--pja-border, #e0e8f0);
    border-radius: 20px;
    font-size: .8rem; font-weight: 700;
    font-family: inherit;
    color: #374151; background: #fff;
    cursor: pointer; transition: all .15s;
}
.pja-cal-view-btn:hover,
.pja-cal-view-btn.active {
    background: var(--pja-green, #2e7d32);
    border-color: var(--pja-green, #2e7d32);
    color: #fff;
}

/* ── Calendar container ──────────────────────────────────────────── */
#pja-fullcalendar {
    background: #fff;
    border: 1px solid var(--pja-border, #e0e8f0);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(26,46,90,.06);
}

/* ── FullCalendar toolbar ─────────────────────────────────────────── */
.fc .fc-toolbar { padding: 1rem 1.25rem .75rem; background: #fff; flex-wrap: wrap; gap: .5rem; }
.fc .fc-toolbar-title { font-size: 1.15rem; font-weight: 800; color: var(--pja-navy, #1a2e5a); letter-spacing: -.02em; }
.fc .fc-button {
    background: #fff; border: 1.5px solid #e0e8f0;
    color: #374151; font-family: inherit;
    font-size: .82rem; font-weight: 700;
    padding: .3rem .8rem; border-radius: 8px;
    transition: all .15s; box-shadow: none; text-transform: none;
}
.fc .fc-button:hover { background: #e8f5e9; border-color: #2e7d32; color: #2e7d32; }
.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active { background: #2e7d32; border-color: #2e7d32; color: #fff; box-shadow: none; }
.fc .fc-button:focus { box-shadow: 0 0 0 3px rgba(46,125,50,.2); outline: none; }
.fc .fc-today-button:disabled { opacity: .45; }

/* ── Column headers ──────────────────────────────────────────────── */
.fc .fc-col-header-cell { background: #f8fafd; border-color: #e0e8f0; padding: .5rem 0; }
.fc .fc-col-header-cell-cushion { font-size: .8rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; text-decoration: none; }
.fc .fc-col-header-cell-cushion:hover { color: #2e7d32; text-decoration: none; }

/* ── Day grid cells ──────────────────────────────────────────────── */
.fc .fc-daygrid-day { border-color: #e0e8f0; }
.fc .fc-daygrid-day-number { font-size: .82rem; font-weight: 600; color: #374151; text-decoration: none; padding: .4rem .6rem; }
.fc .fc-daygrid-day-number:hover { color: #2e7d32; text-decoration: none; }
.fc .fc-day-today { background: #f0f9f0 !important; }
.fc .fc-day-today .fc-daygrid-day-number {
    background: #2e7d32; color: #fff; border-radius: 50%;
    width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; padding: 0; margin: .35rem .5rem;
}
.fc .fc-day-other .fc-daygrid-day-number { color: #d1d5db; }

/* ── Events ──────────────────────────────────────────────────────── */
.fc-event { border-radius: 5px; border: none; font-size: .78rem; font-weight: 600; padding: 2px 6px; cursor: pointer; transition: opacity .15s, transform .1s; }
.fc-event:hover { opacity: .88; transform: translateY(-1px); text-decoration: none; }
.fc-event .fc-event-title { font-weight: 600; }
.fc-event .fc-event-time { opacity: .85; font-size: .72rem; }

/* ── Time grid (week/day) ────────────────────────────────────────── */
.fc .fc-timegrid-slot { border-color: #f0f4f8; height: 44px; }
.fc .fc-timegrid-slot-label { font-size: .75rem; color: #9ca3af; font-weight: 600; }
.fc .fc-timegrid-axis { border-color: #e0e8f0; }
.fc-direction-ltr .fc-timegrid-col-events { margin: 0 3px; }
.fc .fc-timegrid-now-indicator-line { border-color: #e53935; }
.fc .fc-timegrid-now-indicator-arrow { border-top-color: #e53935; border-bottom-color: #e53935; }

/* ── List view ───────────────────────────────────────────────────── */
.fc .fc-list-day-cushion { background: #f8fafd; padding: .5rem 1rem; }
.fc .fc-list-day-text,
.fc .fc-list-day-side-text { font-size: .82rem; font-weight: 700; color: #1a2e5a; text-decoration: none; }
.fc .fc-list-event:hover td { background: #f0f9f0; }
.fc .fc-list-event-title a { color: #1a2e5a; text-decoration: none; font-weight: 600; }
.fc .fc-list-event-title a:hover { color: #2e7d32; }
.fc .fc-list-empty { padding: 3rem; text-align: center; color: #6b7280; }

/* ── Overflow "more" link ────────────────────────────────────────── */
.fc .fc-daygrid-more-link { font-size: .75rem; font-weight: 700; color: #2e7d32; padding: 1px 4px; border-radius: 4px; }
.fc .fc-daygrid-more-link:hover { background: #e8f5e9; text-decoration: none; }

/* ── Popover ─────────────────────────────────────────────────────── */
.fc .fc-popover { border-radius: 10px; border: 1px solid #e0e8f0; box-shadow: 0 8px 24px rgba(26,46,90,.12); font-family: inherit; }
.fc .fc-popover-header { background: #f8fafd; border-radius: 10px 10px 0 0; padding: .5rem .75rem; font-size: .83rem; font-weight: 700; color: #1a2e5a; }

/* ── Event popup ─────────────────────────────────────────────────── */
#pja-cal-popup-overlay { position: fixed; inset: 0; z-index: 400; background: rgba(0,0,0,.3); backdrop-filter: blur(2px); }
#pja-cal-popup {
    position: fixed; z-index: 401; top: 50%; left: 50%; transform: translate(-50%, -50%);
    width: min(420px, calc(100vw - 2rem)); background: #fff; border-radius: 16px;
    box-shadow: 0 20px 60px rgba(26,46,90,.2); overflow: hidden; animation: pjaPopupIn .2s ease;
}
@keyframes pjaPopupIn {
    from { opacity: 0; transform: translate(-50%, -48%) scale(.97); }
    to   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
.pja-cal-popup__color { height: 6px; }
.pja-cal-popup__body { padding: 1.4rem 1.4rem 1.25rem; position: relative; }
.pja-cal-popup__close {
    position: absolute; top: .85rem; right: .85rem; width: 30px; height: 30px; border: none;
    background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .15s; color: #6b7280;
}
.pja-cal-popup__close:hover { background: #e5e7eb; color: #1a2e5a; }
.pja-cal-popup__title {
    display: block; font-size: 1.1rem; font-weight: 800; color: #1a2e5a; text-decoration: none;
    margin-bottom: .9rem; padding-right: 2.5rem; line-height: 1.3;
}
.pja-cal-popup__title:hover { color: #2e7d32; }
.pja-cal-popup__meta { display: flex; align-items: flex-start; gap: .5rem; font-size: .83rem; color: #6b7280; margin-bottom: .45rem; line-height: 1.4; }
.pja-cal-popup__meta::before { flex-shrink: 0; }
.pja-cal-popup__date::before  { content: '📅'; }
.pja-cal-popup__venue::before { content: '📍'; }
.pja-cal-popup__cat::before   { content: '🏷'; }
.pja-cal-popup__intro { font-size: .83rem; color: #6b7280; margin-top: .85rem; line-height: 1.55; border-top: 1px solid #f3f4f6; padding-top: .85rem; }
.pja-cal-popup__cta { display: inline-flex; align-items: center; margin-top: 1rem; padding: .5rem 1.1rem; background: #2e7d32; color: #fff; border-radius: 20px; font-size: .83rem; font-weight: 700; text-decoration: none; transition: background .15s; }
.pja-cal-popup__cta:hover { background: #4caf50; text-decoration: none; color: #fff; }

@media (max-width: 640px) {
    .fc .fc-toolbar { flex-direction: column; align-items: flex-start; }
    .fc .fc-toolbar-title { font-size: 1rem; }
    .fc .fc-button { padding: .25rem .6rem; font-size: .78rem; }
    .pja-cal-view-btn { font-size: .75rem; padding: .25rem .65rem; }
}
@media print {
    #pja-cal-viewbar, .pja-ev-actionbar { display: none; }
    #pja-fullcalendar { box-shadow: none; border: none; }
}

:root { --pja-green: <?php echo $primaryColor; ?>; }


/* ── Filter bar styling & Kleurcorrectie ─────────────────────────── */
.pja-filter-btn { 
    transition: all 0.2s ease-in-out; 
    background-color: transparent !important;
    border-color: #6c757d !important;
    color: #6c757d !important;
}

/* Als er NIET gefilterd wordt, toon iedereen in zijn eigen kleur */
.pja-calendar-filters:not(.filtering-active) .pja-filter-btn[data-cat-id]:not([data-cat-id="all"]) {
    background-color: var(--pja-orig-color) !important;
    border-color: var(--pja-orig-color) !important;
    color: #fff !important;
}

/* Alleen de KNOP die echt actief is krijgt zijn kleur terug tijdens het filteren */
.pja-calendar-filters.filtering-active .pja-filter-btn.active {
    background-color: var(--pja-orig-color) !important;
    border-color: var(--pja-orig-color) !important;
    color: #fff !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    font-weight: bold;
}

/* 'Toon alles' knop specifiek stylen */
.pja-filter-btn[data-cat-id="all"] {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: #fff !important;
}
.pja-calendar-filters.filtering-active .pja-filter-btn[data-cat-id="all"] {
    background-color: transparent !important;
    border-color: #6c757d !important;
    color: #6c757d !important;
}
.pja-filter-btn[data-cat-id="all"].active {
    background-color: #212529 !important;
    border-color: #212529 !important;
    color: #fff !important;
}

</style>




<script>
(function() {
'use strict';

// 1. Veilige opvang van variabelen uit PHP via JSON encoding
var JSON_URL  = <?php echo json_encode($jsonUrl); ?>;
var INIT_VIEW = <?php echo json_encode($initView); ?>;
var LANG      = <?php echo json_encode($langTag ?: 'nl'); ?>;

var calendar = null;
var activeCategoryFilter = 'all';

/* ── View definitions ──────────────────────────────────────────── */
var VIEWS = [
    { key: 'dayGridMonth', label: 'Maand' },
    { key: 'timeGridWeek', label: 'Week'  },
    { key: 'timeGridDay',  label: 'Dag'   },
    { key: 'listMonth',    label: 'Lijst' }
];

/* ── Build view switcher ───────────────────────────────────────── */
function buildViewBar() {
    var bar = document.getElementById('pja-cal-viewbar');
    if (!bar) return;
    
    bar.innerHTML = ''; 
    
    VIEWS.forEach(function(v) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pja-cal-view-btn' + (v.key === INIT_VIEW ? ' active' : '');
        btn.textContent = v.label;
        btn.dataset.view = v.key;
        btn.setAttribute('aria-pressed', v.key === INIT_VIEW ? 'true' : 'false');
        
        btn.addEventListener('click', function() {
            if (!calendar) return;
            calendar.changeView(v.key);
            
            document.querySelectorAll('.pja-cal-view-btn').forEach(function(b) {
                var active = (b === btn);
                b.classList.toggle('active', active);
                b.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        });
        bar.appendChild(btn);
    });
}

/* ── Popup ─────────────────────────────────────────────────────── */
function pjaShowPopup(info) {
    var ev    = info.event;
    var props = ev.extendedProps || {};
    var popup = document.getElementById('pja-cal-popup');
    var over  = document.getElementById('pja-cal-popup-overlay');
    if (!popup) return;

    var colorEl = popup.querySelector('.pja-cal-popup__color');
    if (colorEl) {
        colorEl.style.background = ev.backgroundColor || '#2e7d32';
    }

    var titleEl = popup.querySelector('.pja-cal-popup__title');
    if (titleEl) {
        titleEl.textContent = ev.title;
        titleEl.href = ev.url || '#';
    }

    var ctaEl = popup.querySelector('.pja-cal-popup__cta');
    if (ctaEl) { 
        ctaEl.href = ev.url || '#'; 
    }

    var dateStr = '';
    if (ev.start) {
        var opts = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        dateStr = ev.start.toLocaleDateString('nl-NL', opts);
        if (!ev.allDay && ev.start.getHours() !== 0) {
            dateStr += ' · ' + ev.start.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });
            if (ev.end && ev.end.getHours() !== 0) {
                dateStr += ' – ' + ev.end.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });
            }
        }
    }

    var dateEl  = popup.querySelector('.pja-cal-popup__date');
    var venueEl = popup.querySelector('.pja-cal-popup__venue');
    var catEl   = popup.querySelector('.pja-cal-popup__cat');
    var introEl = popup.querySelector('.pja-cal-popup__intro');

    if (dateEl)  dateEl.textContent  = dateStr;
    if (venueEl) venueEl.textContent  = props.venue || '';
    if (catEl)   catEl.textContent    = props.category || '';
    if (introEl) introEl.textContent  = props.intro || '';

    popup.querySelectorAll('.pja-cal-popup__meta').forEach(function(el) {
        el.style.display = el.textContent.trim() ? '' : 'none';
    });
    
    if (introEl) {
        introEl.style.display = introEl.textContent.trim() ? '' : 'none';
    }

    popup.removeAttribute('hidden');
    if (over) over.removeAttribute('hidden');
    
    var closeBtn = popup.querySelector('.pja-cal-popup__close');
    if (closeBtn) closeBtn.focus();
}

window.pjaClosePopup = function() {
    var popup = document.getElementById('pja-cal-popup');
    var over  = document.getElementById('pja-cal-popup-overlay');
    if (popup) popup.setAttribute('hidden', 'true');
    if (over)  over.setAttribute('hidden', 'true');
};

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') window.pjaClosePopup();
});

/* ── Init FullCalendar ─────────────────────────────────────────── */
function initCalendar() {
    var el = document.getElementById('pja-fullcalendar');
    
    // Controleer of het HTML element én de FullCalendar library bestaan
    if (!el || typeof FullCalendar === 'undefined') {
        var fallback = document.getElementById('pja-cal-fallback');
        var loading  = document.getElementById('pja-cal-loading');
        if (fallback) fallback.style.display = 'block';
        if (loading)  loading.style.display  = 'none';
        return;
    }

    var loadingEl = document.getElementById('pja-cal-loading');
    if (loadingEl) loadingEl.style.display = 'none';

    calendar = new FullCalendar.Calendar(el, {
        locale:       LANG === 'nl' ? 'nl' : LANG,
        initialView:  INIT_VIEW,
        firstDay:     1,
        height:       'auto',
        expandRows:   true,
        dayMaxEvents: 4,
        navLinks:     true,

        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  ''
        },
        buttonText: {
            today: 'Vandaag',
            list:  'Lijst'
        },

        slotMinTime:       '07:00:00',
        slotMaxTime:       '23:00:00',
        slotDuration:      '00:30:00',
        slotLabelInterval: '01:00:00',
        allDayText:        'Hele dag',
        nowIndicator:      true,

        events: function(info, successCallback, failureCallback) {
            var glue = JSON_URL.indexOf('?') !== -1 ? '&' : '?';
            var finalUrl = JSON_URL + glue + 'start=' + info.startStr.substring(0, 10) + '&end=' + info.endStr.substring(0, 10);
            
            var muniSelect = document.getElementById('pja-municipality-select');
            var selectedMunicipality = muniSelect ? muniSelect.value : 'all';
            
            finalUrl += '&municipality=' + encodeURIComponent(selectedMunicipality);
            finalUrl += '&catid=' + encodeURIComponent(activeCategoryFilter);

            fetch(finalUrl)
                .then(function(response) {
                    if (!response.ok) throw new Error('Netwerkresponsfout');
                    return response.json();
                })
                .then(function(data) {
                    successCallback(Array.isArray(data) ? data : []);
                })
                .catch(function(error) {
                    console.error('Fout bij laden van kalenderdata:', error);
                    failureCallback(error);
                });
        },

        loading: function(isLoading) {
            var wrap = document.getElementById('pja-fullcalendar');
            if (wrap) wrap.style.opacity = isLoading ? '.6' : '1';
        },

        eventClick: function(info) {
            info.jsEvent.preventDefault();
            pjaShowPopup(info);
        },

        navLinkDayClick: function(date) {
            calendar.changeView('timeGridDay', date);
            document.querySelectorAll('.pja-cal-view-btn').forEach(function(b) {
                var active = (b.dataset.view === 'timeGridDay');
                b.classList.toggle('active', active);
                b.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        },

        eventDidMount: function(info) {
            var props = info.event.extendedProps || {};
            var tip = info.event.title;
            if (props.venue) tip += '\n📍 ' + props.venue;
            info.el.setAttribute('title', tip);
        }
    });

    calendar.render();
    
    // 2. Categorie filterknoppen koppelen
    var filterContainer = document.querySelector('.pja-calendar-filters');
    var buttons = document.querySelectorAll('.pja-filter-btn');

    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            var catId = String(this.dataset.catId).trim();
            
            buttons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');

            activeCategoryFilter = catId;
            if (filterContainer) {
                if (catId === 'all') {
                    filterContainer.classList.remove('filtering-active');
                } else {
                    filterContainer.classList.add('filtering-active');
                }
            }

            calendar.refetchEvents();
        });
    });

    // 3. Gemeente-dropdown koppelen
    var municipalitySelect = document.getElementById('pja-municipality-select');
    var savedMunicipality = localStorage.getItem('pja_remembered_municipality');
    if (savedMunicipality && municipalitySelect) {
        // Zet de dropdown direct op de opgeslagen waarde
        municipalitySelect.value = savedMunicipality;
    }
    if (municipalitySelect) {
        municipalitySelect.addEventListener('change', function() {
        localStorage.setItem('pja_remembered_municipality', this.value);
            calendar.refetchEvents();
        });
    }
}

// 4. Gegarandeerd wachten totdat de complete DOM en HTML-tags zijn ingeladen
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        buildViewBar();
        initCalendar();
    });
} else {
    buildViewBar();
    initCalendar();
}

})();
</script>