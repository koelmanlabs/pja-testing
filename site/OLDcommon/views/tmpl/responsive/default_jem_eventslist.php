<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * Responsive eventslist — modern card design matching tpl_planjeagenda
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$showiconsineventdata  = $this->params->get('showiconsineventdata', 1);
$showiconsineventtitle = $this->params->get('showiconsineventtitle', 1);

// Dutch month abbreviations
$months = ['jan','feb','mrt','apr','mei','jun','jul','aug','sep','okt','nov','dec'];
?>

<?php
// ── Load categories + current filter state ────────────────────────────
$filterCategories = [];
try {
    $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
    $db->setQuery(
        $db->getQuery(true)
            ->select(['id', 'catname'])
            ->from('#__pja_categories')
            ->where('published = 1')
            ->where('alias != ' . $db->quote('root'))
            ->order('catname ASC')
    );
    $filterCategories = $db->loadObjectList() ?: [];
} catch (\Throwable $e) { $filterCategories = []; }

$app             = \Joomla\CMS\Factory::getApplication();
$currentCatId    = (int)$app->input->get('filter_catid', 0);
$currentProvince = $app->input->getString('filter_venue_state', '');
$currentDateFrom = $app->input->getString('filter_date_from', '');
$currentDateTo   = $app->input->getString('filter_date_to', '');
$currentVenueText = $app->input->getString('filter_venue_text', '');
$currentCityText  = $app->input->getString('filter_city_text', '');
$currentTagId     = (int)$app->input->get('filter_tag_id', 0);

// Load available tags for this component
$availableTags = [];
try {
    $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
    $db->setQuery(
        $db->getQuery(true)
            ->select('DISTINCT t.id, t.title')
            ->from('#__tags AS t')
            ->join('INNER', '#__contentitem_tag_map AS tm ON tm.tag_id = t.id AND tm.type_alias = ' . $db->quote('com_planjeagenda.event'))
            ->join('INNER', '#__pja_events AS e ON e.id = tm.content_item_id AND e.published = 1')
            ->where('t.published = 1')
            ->order('t.title ASC')
    );
    $availableTags = $db->loadObjectList() ?: [];
} catch (\Throwable $e) { $availableTags = []; }
$currentMonth     = $this->lists['month'] ?? '';
$hasMoreFilters   = $currentCatId > 0 || $currentProvince !== ''
                 || $currentDateFrom !== '' || $currentDateTo !== ''
                 || $currentVenueText !== '' || $currentCityText !== ''
                 || $currentMonth !== '' || $currentTagId > 0;

$provinces = [
    'Groningen','Friesland','Drenthe','Overijssel','Flevoland',
    'Gelderland','Utrecht','Noord-Holland','Zuid-Holland',
    'Zeeland','Noord-Brabant','Limburg',
];
?>
<div class="pja-ev-filterbar">

    <?php /* ── Main search row ─────────────────────────────────────── */ ?>
    <div class="pja-ev-filterrow">

        <div class="pja-ev-search-wrap">
            <svg class="pja-ev-si" width="14" height="14" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <circle cx="8.5" cy="8.5" r="5.75" stroke="#9ca3af" stroke-width="1.75"/>
                <path d="M13.5 13.5l3 3" stroke="#9ca3af" stroke-width="1.75" stroke-linecap="round"/>
            </svg>
            <input type="text" name="filter_search" id="filter_search"
                   class="pja-ev-search-input"
                   placeholder="Zoeken..."
                   value="<?php echo htmlspecialchars($this->lists['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                   onchange="document.adminForm.submit();">
            <input type="hidden" name="filter_type" value="1">
        </div>

        <button class="pja-ev-btn-search" type="submit">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <circle cx="8.5" cy="8.5" r="5.75" stroke="currentColor" stroke-width="2"/>
                <path d="M13.5 13.5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Zoeken
        </button>

        <button class="pja-ev-btn-clear" type="button"
                onclick="['filter_search','filter_month','filter_catid','filter_venue_state','filter_date_from','filter_date_to','filter_venue_text','filter_city_text','filter_tag_id'].forEach(function(id){var el=document.getElementById(id);if(el)el.value='';});document.adminForm.submit();">
            Wissen
        </button>

        <button type="button" id="pja-more-toggle"
                class="pja-ev-btn-more<?php echo $hasMoreFilters ? ' pja-ev-btn-more--on' : ''; ?>"
                aria-expanded="<?php echo $hasMoreFilters ? 'true' : 'false'; ?>"
                onclick="pjaToggleFilters()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="16" y2="12"/><line x1="4" y1="18" x2="12" y2="18"/>
            </svg>
            Filters<?php if ($hasMoreFilters): ?> <span class="pja-ev-dot"></span><?php endif; ?>
        </button>

        <?php if ($this->settings->get('global_display', 1)): ?>
        <div class="pja-ev-limit">
            <?php echo $this->pagination?->getLimitBox(); ?>
        </div>
        <?php endif; ?>

    </div>

    <?php /* ── Active chips ─────────────────────────────────────────── */ ?>
    <?php if ($hasMoreFilters): ?>
    <div class="pja-ev-chips">
        <?php if ($currentProvince): ?>
            <span class="pja-ev-chip">
                📍 <?php echo htmlspecialchars($currentProvince); ?>
                <button type="button" onclick="document.getElementById('filter_venue_state').value='';document.adminForm.submit();">&times;</button>
            </span>
        <?php endif; ?>
        <?php if ($currentCatId > 0):
            $cn = ''; foreach ($filterCategories as $c) { if ((int)$c->id === $currentCatId) { $cn = $c->catname; break; } }
        ?>
            <span class="pja-ev-chip">
                🏷 <?php echo htmlspecialchars($cn); ?>
                <button type="button" onclick="document.getElementById('filter_catid').value='';document.adminForm.submit();">&times;</button>
            </span>
        <?php endif; ?>
        <?php if ($currentDateFrom || $currentDateTo): ?>
            <span class="pja-ev-chip">
                📅 <?php echo ($currentDateFrom ?: '…') . ' → ' . ($currentDateTo ?: '…'); ?>
                <button type="button" onclick="document.getElementById('filter_date_from').value='';document.getElementById('filter_date_to').value='';document.adminForm.submit();">&times;</button>
            </span>
        <?php endif; ?>
        <?php if ($currentTagId > 0):
            $tagName = '';
            foreach ($availableTags as $t) { if ((int)$t->id === $currentTagId) { $tagName = $t->title; break; } }
        ?>
            <span class="pja-ev-chip">
                🏷️ <?php echo htmlspecialchars($tagName); ?>
                <button type="button" onclick="document.getElementById('filter_tag_id').value='0';document.adminForm.submit();">&times;</button>
            </span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php /* ── Expandable filters ──────────────────────────────────── */ ?>
    <div id="pja-more-filters" class="pja-ev-morepanel" <?php echo $hasMoreFilters ? '' : 'hidden'; ?>>

        <div class="pja-ev-fgroup">
            <label class="pja-ev-flabel" for="filter_month">📅 Maand</label>
            <div class="pja-ev-month-wrap">
                <input type="month" name="filter_month" id="filter_month"
                       class="pja-ev-fsel"
                       pattern="[0-9]{4}-[0-9]{2}"
                       placeholder="Maand"
                       value="<?php echo htmlspecialchars($this->lists['month'] ?? '', ENT_QUOTES); ?>">
            </div>
        </div>

        <div class="pja-ev-fgroup">
            <label class="pja-ev-flabel" for="filter_venue_state">📍 Provincie</label>
            <select name="filter_venue_state" id="filter_venue_state" class="pja-ev-fsel"
                    onchange="document.adminForm.submit()">
                <option value="">Alle provincies</option>
                <?php foreach ($provinces as $p): ?>
                    <option value="<?php echo htmlspecialchars($p); ?>"
                        <?php echo $currentProvince === $p ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($filterCategories)): ?>
        <div class="pja-ev-fgroup">
            <label class="pja-ev-flabel" for="filter_catid">🏷 Categorie</label>
            <select name="filter_catid" id="filter_catid" class="pja-ev-fsel"
                    onchange="document.adminForm.submit()">
                <option value="">Alle categorieën</option>
                <?php foreach ($filterCategories as $cat): ?>
                    <option value="<?php echo (int)$cat->id; ?>"
                        <?php echo $currentCatId === (int)$cat->id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat->catname); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="pja-ev-fgroup">
            <label class="pja-ev-flabel" for="filter_date_from">📅 Vanaf</label>
            <input type="date" name="filter_date_from" id="filter_date_from"
                   class="pja-ev-fsel"
                   value="<?php echo htmlspecialchars($currentDateFrom); ?>">
        </div>

        <div class="pja-ev-fgroup">
            <label class="pja-ev-flabel" for="filter_date_to">📅 Tot</label>
            <input type="date" name="filter_date_to" id="filter_date_to"
                   class="pja-ev-fsel"
                   value="<?php echo htmlspecialchars($currentDateTo); ?>">
        </div>

    </div>

</div>
<script>
function pjaToggleFilters() {
    var p = document.getElementById('pja-more-filters');
    var t = document.getElementById('pja-more-toggle');
    if (p.hasAttribute('hidden')) {
        p.removeAttribute('hidden');
        t.setAttribute('aria-expanded', 'true');
    } else {
        p.setAttribute('hidden', '');
        t.setAttribute('aria-expanded', 'false');
    }
}
// Style the month-picker selects (JS replaces the input with two selects)
(function() {
    var wrap = document.querySelector('.pja-ev-month-wrap');
    if (!wrap) return;
    function styleSelects() {
        wrap.querySelectorAll('select').forEach(function(s) {
            s.style.cssText = 'height:36px;border:1.5px solid #e0e8f0;border-radius:8px;font-size:.83rem;padding:0 .5rem;font-family:inherit;color:#1a2e5a;background:#fff;outline:none;';
        });
    }
    styleSelects();
    new MutationObserver(styleSelects).observe(wrap, {childList:true, subtree:true});
})();
</script>

<?php /* ── Sort strip — minimal, date+title only ─────────────────────── */ ?>
<div class="pja-ev-sortbar">
    <span class="pja-ev-sortlabel"><?php echo Text::_('JGLOBAL_SORT_BY'); ?></span>
    <?php
    $sortFields = [
        'a.dates' => Text::_('com_planjeagenda_TABLE_DATE'),
        'a.title' => Text::_('com_planjeagenda_TABLE_TITLE'),
    ];
    foreach ($sortFields as $field => $label):
        $isActive = $this->lists['order'] === $field;
        $dir      = ($isActive && $this->lists['order_Dir'] === 'ASC') ? 'DESC' : 'ASC';
        $arrow    = $isActive ? ($this->lists['order_Dir'] === 'ASC' ? ' ↑' : ' ↓') : '';
    ?>
    <button type="button"
            class="pja-ev-sortbtn<?php echo $isActive ? ' pja-ev-sortbtn--active' : ''; ?>"
            onclick="Joomla.tableOrdering('<?php echo $field; ?>','<?php echo $dir; ?>','')">
        <?php echo $label . $arrow; ?>
    </button>
    <?php endforeach; ?>
</div>

<?php /* ── Event cards ─────────────────────────────────────────────────── */ ?>
<?php if (empty($this->rows)): ?>
    <div class="pja-ev-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <path d="M16 2v4M8 2v4M3 10h18"/>
            <path d="M9 16l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p><?php echo Text::_('com_planjeagenda_NO_EVENTS'); ?></p>
    </div>
<?php else: ?>
<div class="pja-ev-list">
<?php foreach ($this->rows as $row):
    $eventUrl   = Route::_(PlanjeagendaHelperRoute::getEventRoute($row->slug));
    $isFeatured = !empty($row->featured);

    // Parse date
    $dateDay = $dateMon = $dateTime = '';
    if (!empty($row->dates) && $row->dates !== '0000-00-00') {
        $ts      = strtotime($row->dates);
        $dateDay = date('j', $ts);
        $dateMon = $months[(int)date('n', $ts) - 1];
        if (!empty($row->times) && substr($row->times,0,5) !== '00:00' && $this->jemsettings->showtime) {
            $dateTime = substr($row->times, 0, 5);
        }
    }

    // Categories
    $cats = !empty($row->categories)
        ? PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist)
        : [];
?>
<div class="pja-ev-card<?php echo $isFeatured ? ' pja-ev-card--featured' : ''; ?>"
     itemscope itemtype="https://schema.org/Event">
    <meta itemprop="url" content="<?php echo $eventUrl; ?>">

    <?php /* Date badge */ ?>
    <div class="pja-ev-date" aria-label="<?php echo $dateDay . ' ' . $dateMon; ?>">
        <span class="pja-ev-date__day"><?php echo $dateDay ?: '—'; ?></span>
        <span class="pja-ev-date__mon"><?php echo $dateMon; ?></span>
        <?php if ($dateTime): ?>
            <span class="pja-ev-date__time"><?php echo $dateTime; ?></span>
        <?php endif; ?>
    </div>

    <?php /* Optional event image */ ?>
    <?php if ($this->jemsettings->showeventimage == 1 && !empty($row->datimage)): ?>
    <div class="pja-ev-thumb">
        <?php
        $dimage = PlanjeagendaImage::flyercreator($row->datimage, 'event');
        echo PlanjeagendaOutput::flyer($row, $dimage, 'event');
        ?>
    </div>
    <?php endif; ?>

    <?php /* Main content */ ?>
    <div class="pja-ev-body">
        <div class="pja-ev-title" itemprop="name">
            <?php if ($this->jemsettings->showdetails == 1): ?>
                <a href="<?php echo $eventUrl; ?>"><?php echo $this->escape($row->title); ?></a>
            <?php else: ?>
                <?php echo $this->escape($row->title); ?>
            <?php endif; ?>
            <?php echo PlanjeagendaOutput::publishstateicon($row); ?>
            <?php if ($showiconsineventtitle) echo PlanjeagendaOutput::recurrenceicon($row); ?>
            <?php if ($isFeatured): ?>
                <svg class="pja-ev-star" width="13" height="13" viewBox="0 0 24 24" fill="#f59e0b" aria-hidden="true">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            <?php endif; ?>
        </div>

        <?php if ($this->params->get('show_introtext_events') == 1 && !empty($row->introtext)): ?>
            <div class="pja-ev-intro"><?php echo strip_tags($row->introtext); ?></div>
        <?php endif; ?>

        <div class="pja-ev-meta">
            <?php if ($this->jemsettings->showlocate == 1 && !empty($row->venue)): ?>
            <span class="pja-ev-meta__item">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                <?php if ($this->jemsettings->showlinkvenue == 1 && !empty($row->venueslug)): ?>
                    <a href="<?php echo Route::_(PlanjeagendaHelperRoute::getVenueRoute($row->venueslug)); ?>"
                       itemprop="location"><?php echo $this->escape($row->venue); ?></a>
                <?php else: ?>
                    <span itemprop="location"><?php echo $this->escape($row->venue); ?></span>
                <?php endif; ?>
            </span>
            <?php endif; ?>

            <?php if ($this->jemsettings->showcity == 1 && !empty($row->city)): ?>
            <span class="pja-ev-meta__item">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="1"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                <?php echo $this->escape($row->city); ?>
                <?php if ($this->jemsettings->showstate == 1 && !empty($row->state)): ?>
                    <span class="pja-ev-meta__sep">·</span><?php echo $this->escape($row->state); ?>
                <?php endif; ?>
            </span>
            <?php elseif ($this->jemsettings->showstate == 1 && !empty($row->state)): ?>
            <span class="pja-ev-meta__item">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3V6z"/></svg>
                <?php echo $this->escape($row->state); ?>
            </span>
            <?php endif; ?>
        </div>

        <?php /* Event tags */ ?>
        <?php if (!empty($row->event_tags)): ?>
        <div class="pja-ev-tag-row">
            <?php
            $tagTitles = explode(',', $row->event_tags);
            $tagIds    = !empty($row->event_tag_ids) ? explode(',', $row->event_tag_ids) : [];
            foreach ($tagTitles as $i => $tagTitle):
                $tagId = (int)($tagIds[$i] ?? 0);
                $tagUrl = Route::_('index.php?option=com_planjeagenda&view=eventslist&filter_tag_id=' . $tagId);
            ?>
            <a href="<?php echo htmlspecialchars($tagUrl); ?>" class="pja-ev-tag-pill">
                <?php echo htmlspecialchars(trim($tagTitle)); ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php /* Schema.org hidden address */ ?>
        <div itemtype="https://schema.org/PostalAddress" itemscope itemprop="location" style="display:none">
            <meta itemprop="streetAddress"   content="<?php echo $this->escape($row->street ?? ''); ?>">
            <meta itemprop="addressLocality" content="<?php echo $this->escape($row->city   ?? ''); ?>">
            <meta itemprop="addressRegion"   content="<?php echo $this->escape($row->state  ?? ''); ?>">
            <meta itemprop="postalCode"      content="<?php echo $this->escape($row->postalCode ?? ''); ?>">
        </div>
    </div>

    <?php /* Right aside: category + attendees */ ?>
    <div class="pja-ev-aside">
        <?php if ($this->jemsettings->showcat == 1 && !empty($cats)): ?>
            <div class="pja-ev-cats">
                <?php foreach ($cats as $cat): ?>
                    <span class="pja-ev-cat"><?php echo strip_tags($cat); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($this->jemsettings->showatte == 1 && isset($row->maxplaces) && $row->maxplaces > 0): ?>
            <span class="pja-ev-att">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
                <?php echo ($row->regCount ?? '0') . '/' . $row->maxplaces; ?>
            </span>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<style>
/* ── Eventslist card design — scoped to #klevents ────────────────────── */
#klevents {
    font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
}

/* Filter bar */
#klevents .pja-ev-filterbar {
    background: #fff;
    border: 1px solid #e0e8f0;
    border-radius: 10px;
    padding: .85rem 1.1rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 4px rgba(26,46,90,.06);
}
#klevents .pja-ev-filterrow {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .45rem;
}
#klevents .pja-ev-search-wrap {
    flex: 1;
    min-width: 160px;
    display: flex;
    align-items: center;
    gap: .4rem;
    background: #f8fafd;
    border: 1.5px solid #e0e8f0;
    border-radius: 8px;
    padding: 0 .75rem;
    height: 36px;
    transition: border-color .15s;
}
#klevents .pja-ev-search-wrap:focus-within {
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46,125,50,.09);
    background: #fff;
}
#klevents .pja-ev-si { flex-shrink: 0; }
#klevents .pja-ev-search-input {
    flex: 1; border: none; background: transparent; outline: none;
    font-size: .875rem; font-family: inherit; color: #1a2e5a; min-width: 0;
}
#klevents .pja-ev-search-input::placeholder { color: #9ca3af; }
/* Month wrap in expanded panel — style native picker + JS fallback selects */
#klevents .pja-ev-month-wrap { display: flex; align-items: center; gap: .3rem; flex-wrap: wrap; }
#klevents .pja-ev-month-wrap input[type="month"] {
    height: 36px; padding: 0 .7rem;
    border: 1.5px solid #e0e8f0; border-radius: 8px;
    font-size: .85rem; font-family: inherit;
    color: #1a2e5a; background: #fff; outline: none;
    transition: border-color .15s; min-width: 130px;
}
#klevents .pja-ev-month-wrap input[type="month"]:focus { border-color: #2e7d32; }
#klevents .pja-ev-month-wrap select {
    height: 36px !important;
    border: 1.5px solid #e0e8f0 !important;
    border-radius: 8px !important;
    font-size: .83rem !important;
    padding: 0 .5rem !important;
    font-family: inherit !important;
    color: #1a2e5a !important;
    background: #fff !important;
    outline: none !important;
}
#klevents .pja-ev-month-wrap select:focus { border-color: #2e7d32 !important; }
/* Action buttons */
#klevents .pja-ev-btn-search {
    height: 36px; padding: 0 1.1rem;
    background: #2e7d32; color: #fff;
    border: none; border-radius: 8px;
    font-size: .875rem; font-weight: 700;
    font-family: inherit; cursor: pointer;
    display: inline-flex; align-items: center; gap: .4rem;
    transition: background .15s; white-space: nowrap;
}
#klevents .pja-ev-btn-search:hover { background: #4caf50; }
#klevents .pja-ev-btn-clear {
    height: 36px; padding: 0 .9rem;
    background: #fff; color: #6b7280;
    border: 1.5px solid #e0e8f0; border-radius: 8px;
    font-size: .875rem; font-family: inherit;
    cursor: pointer; transition: all .15s; white-space: nowrap;
}
#klevents .pja-ev-btn-clear:hover { background: #f8fafd; color: #1a2e5a; }
#klevents .pja-ev-btn-more {
    height: 36px; padding: 0 .9rem;
    background: #fff; color: #374151;
    border: 1.5px solid #e0e8f0; border-radius: 8px;
    font-size: .83rem; font-weight: 600;
    font-family: inherit; cursor: pointer;
    display: inline-flex; align-items: center; gap: .4rem;
    transition: all .15s; white-space: nowrap;
}
#klevents .pja-ev-btn-more:hover { background: #f8fafd; border-color: #2e7d32; }
#klevents .pja-ev-btn-more--on { border-color: #2e7d32; color: #2e7d32; background: #e8f5e9; }
#klevents .pja-ev-dot {
    width: 7px; height: 7px;
    background: #2e7d32; border-radius: 50%; display: inline-block;
}
#klevents .pja-ev-limit { display: flex; align-items: center; margin-left: auto; }
#klevents .pja-ev-limit select {
    height: 32px !important; border: 1.5px solid #e0e8f0 !important;
    border-radius: 6px !important; font-size: .8rem !important;
    padding: 0 .5rem !important; font-family: inherit !important; color: #6b7280 !important;
}
/* Chips */
#klevents .pja-ev-chips {
    display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem;
}
#klevents .pja-ev-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .2rem .65rem;
    background: #e8f5e9; border: 1px solid rgba(46,125,50,.2);
    border-radius: 20px; font-size: .75rem; font-weight: 600; color: #2e7d32;
}
#klevents .pja-ev-chip button {
    background: none; border: none; color: #81c784;
    font-size: .9rem; line-height: 1; padding: 0; cursor: pointer;
}
#klevents .pja-ev-chip button:hover { color: #2e7d32; }
/* Expandable more-filters panel */
#klevents .pja-ev-morepanel {
    display: flex; flex-wrap: wrap; gap: .75rem 1.25rem;
    align-items: flex-end;
    border-top: 1px solid #e0e8f0;
    padding-top: .85rem; margin-top: .6rem;
}
#klevents .pja-ev-morepanel[hidden] { display: none; }
#klevents .pja-ev-fgroup { display: flex; flex-direction: column; gap: .3rem; min-width: 130px; }
#klevents .pja-ev-flabel {
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; color: #6b7280;
}
#klevents .pja-ev-fsel {
    height: 36px; padding: 0 .7rem;
    border: 1.5px solid #e0e8f0; border-radius: 8px;
    font-size: .85rem; font-family: inherit;
    color: #1a2e5a; background: #fff; outline: none;
    transition: border-color .15s; -webkit-appearance: auto;
}
#klevents .pja-ev-fsel:focus { border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46,125,50,.09); }

/* Sort bar */
#klevents .pja-ev-sortbar {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
    margin-bottom: .85rem;
    font-size: .78rem;
}
#klevents .pja-ev-sortlabel {
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-size: .72rem;
}
#klevents .pja-ev-sortbtn {
    padding: .25rem .7rem;
    border: 1px solid #e0e8f0;
    border-radius: 20px;
    background: #fff;
    font-size: .78rem;
    font-family: inherit;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: background .15s, border-color .15s;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}
#klevents .pja-ev-sortbtn:hover,
#klevents .pja-ev-sortbtn:focus,
#klevents .pja-ev-sortbtn--active { background: #e8f5e9; border-color: #2e7d32; color: #2e7d32; }

/* Event cards */
#klevents .pja-ev-list {
    display: flex;
    flex-direction: column;
    gap: .55rem;
}
#klevents .pja-ev-card {
    background: #fff;
    border: 1px solid #e0e8f0;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(26,46,90,.07);
    display: flex;
    align-items: stretch;
    overflow: hidden;
    transition: box-shadow .15s, transform .12s;
}
#klevents .pja-ev-card:hover {
    box-shadow: 0 4px 16px rgba(26,46,90,.12);
    transform: translateY(-2px);
}
#klevents .pja-ev-card--featured { border-left: 3px solid #f59e0b; }

/* Date badge */
#klevents .pja-ev-date {
    flex: 0 0 64px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: .75rem .4rem;
    background: #e8f5e9;
    border-right: 1px solid #e0e8f0;
    text-align: center;
    gap: .05rem;
}
#klevents .pja-ev-date__day {
    font-size: 1.55rem;
    font-weight: 800;
    color: #2e7d32;
    line-height: 1;
}
#klevents .pja-ev-date__mon {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #2e7d32;
}
#klevents .pja-ev-date__time {
    font-size: .62rem;
    color: #6b7280;
    margin-top: .2rem;
}

/* Thumbnail */
#klevents .pja-ev-thumb {
    flex: 0 0 72px;
    overflow: hidden;
}
#klevents .pja-ev-thumb img {
    width: 72px;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Body */
#klevents .pja-ev-body {
    flex: 1;
    padding: .75rem .9rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
    gap: .25rem;
}
#klevents .pja-ev-title {
    font-size: .93rem;
    font-weight: 700;
    color: #1a2e5a;
    display: flex;
    align-items: center;
    gap: .3rem;
    flex-wrap: wrap;
}
#klevents .pja-ev-title a {
    color: #1a2e5a;
    text-decoration: none;
    transition: color .15s;
}
#klevents .pja-ev-title a:hover { color: #2e7d32; }
#klevents .pja-ev-star { flex-shrink: 0; }
#klevents .pja-ev-intro {
    font-size: .78rem;
    color: #6b7280;
    line-height: 1.45;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
}
#klevents .pja-ev-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .25rem .65rem;
    font-size: .75rem;
    color: #6b7280;
    align-items: center;
}
#klevents .pja-ev-meta__item {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
}
#klevents .pja-ev-meta__item svg { flex-shrink: 0; color: #2e7d32; }
#klevents .pja-ev-meta__item a { color: #6b7280; text-decoration: none; }
#klevents .pja-ev-meta__item a:hover { color: #2e7d32; }
#klevents .pja-ev-meta__sep { color: #d1d5db; }

/* Aside */
#klevents .pja-ev-aside {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    padding: .75rem .9rem;
    gap: .4rem;
}
#klevents .pja-ev-cats { display: flex; flex-direction: column; align-items: flex-end; gap: .25rem; }
#klevents .pja-ev-cat {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: .68rem;
    font-weight: 700;
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid rgba(46,125,50,.18);
    white-space: nowrap;
}
#klevents .pja-ev-att {
    font-size: .72rem;
    color: #9ca3af;
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    white-space: nowrap;
}
#klevents .pja-ev-att svg { color: #2e7d32; }

/* Empty state */
#klevents .pja-ev-empty {
    padding: 2.5rem 1.5rem;
    text-align: center;
    background: #fff;
    border: 1.5px dashed #e0e8f0;
    border-radius: 10px;
    color: #9ca3af;
}
#klevents .pja-ev-empty svg { margin: 0 auto .75rem; color: #d1d5db; }
#klevents .pja-ev-empty p { font-size: .9rem; margin: 0; }

/* Copyright line */
#klevents .copyright { font-size: .72rem; color: #d1d5db; margin-top: 1rem; text-align: right; }
#klevents .copyright a { color: #d1d5db; }

/* More filters toggle button */
#klevents .pja-ev-btn-more {
    height: 36px;
    padding: 0 .9rem;
    background: #fff;
    color: #374151;
    border: 1.5px solid #e0e8f0;
    border-radius: 8px;
    font-size: .83rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: background .15s, border-color .15s;
    position: relative;
    white-space: nowrap;
}
#klevents .pja-ev-btn-more:hover { background: #f8fafd; border-color: #2e7d32; }
#klevents .pja-ev-btn-more--active { border-color: #2e7d32; color: #2e7d32; background: #e8f5e9; }
#klevents .pja-ev-active-dot {
    width: 7px; height: 7px;
    background: #2e7d32;
    border-radius: 50%;
    display: inline-block;
}

/* Search group */
#klevents .pja-ev-searchgroup {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex: 1;
    min-width: 0;
}
#klevents .pja-ev-searchgroup .form-select {
    height: 36px;
    border: 1.5px solid #e0e8f0;
    border-radius: 8px;
    font-size: .83rem;
    padding: 0 .6rem;
    font-family: inherit;
    color: #1a2e5a;
    background: #fff;
    flex-shrink: 0;
}

/* More filters panel */
#klevents .pja-ev-morefilters {
    border-top: 1px solid #e0e8f0;
    padding-top: .85rem;
    margin-top: .25rem;
}
#klevents .pja-ev-morefilters[hidden] { display: none; }
#klevents .pja-ev-morerow {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem 1.25rem;
    align-items: flex-end;
}
#klevents .pja-ev-filtergroup {
    display: flex;
    flex-direction: column;
    gap: .3rem;
    min-width: 140px;
}
#klevents .pja-ev-filterlabel {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: .3rem;
}
#klevents .pja-ev-filterlabel svg { color: #2e7d32; }
#klevents .pja-ev-select {
    height: 36px;
    padding: 0 .7rem;
    border: 1.5px solid #e0e8f0;
    border-radius: 8px;
    font-size: .85rem;
    font-family: inherit;
    color: #1a2e5a;
    background: #fff;
    outline: none;
    transition: border-color .15s;
    -webkit-appearance: auto;
}
#klevents .pja-ev-select:focus { border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46,125,50,.1); }

/* Active filter chips */
#klevents .pja-ev-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
    margin-top: .75rem;
}
#klevents .pja-ev-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .25rem .65rem;
    background: #e8f5e9;
    border: 1px solid rgba(46,125,50,.25);
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 600;
    color: #2e7d32;
}
#klevents .pja-ev-chip button {
    background: none;
    border: none;
    color: #81c784;
    font-size: .95rem;
    line-height: 1;
    padding: 0;
    cursor: pointer;
    font-family: inherit;
}
#klevents .pja-ev-chip button:hover { color: #2e7d32; }

/* Responsive */
@media (max-width: 580px) {
    #klevents .pja-ev-aside { display: none; }
    #klevents .pja-ev-date { flex: 0 0 52px; }
    #klevents .pja-ev-date__day { font-size: 1.25rem; }
    #klevents .pja-ev-filterrow { flex-direction: column; align-items: stretch; }
    #klevents .pja-ev-input { min-width: 0; }
}
</style>
