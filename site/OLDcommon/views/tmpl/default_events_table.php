<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * Eventslist — modern card layout matching tpl_planjeagenda design
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$paramShowIconsOrder    = $this->params->get('showiconsinorder', 1);
$showiconsineventtitle  = $this->params->get('showiconsineventtitle', 1);
$showiconsineventdata   = $this->params->get('showiconsineventdata', 1);
?>
<style>
/* ── Eventslist modern card layout ────────────────────────────────────── */
#klevents {
    --ev-green:       #2e7d32;
    --ev-green-light: #4caf50;
    --ev-green-pale:  #e8f5e9;
    --ev-navy:        #1a2e5a;
    --ev-border:      #e0e8f0;
    --ev-muted:       #6b7280;
    --ev-bg:          #f8fafd;
    --ev-white:       #ffffff;
    --ev-shadow-sm:   0 1px 4px rgba(26,46,90,.07);
    --ev-shadow-md:   0 4px 16px rgba(26,46,90,.11);
    --ev-radius:      10px;
    font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
}

/* Filter bar */
#klevents .pja-filter-bar {
    background: var(--ev-white);
    border: 1px solid var(--ev-border);
    border-radius: var(--ev-radius);
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: var(--ev-shadow-sm);
}
#klevents .pja-filter-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .6rem;
    margin-bottom: .6rem;
}
#klevents .pja-filter-row:last-child { margin-bottom: 0; }
#klevents .pja-filter-label {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--ev-muted);
    white-space: nowrap;
}
#klevents .pja-filter-select,
#klevents .pja-filter-input {
    height: 36px;
    padding: 0 .75rem;
    border: 1.5px solid var(--ev-border);
    border-radius: 8px;
    font-size: .875rem;
    font-family: inherit;
    color: var(--ev-navy);
    background: var(--ev-white);
    transition: border-color .15s, box-shadow .15s;
    outline: none;
}
#klevents .pja-filter-select:focus,
#klevents .pja-filter-input:focus {
    border-color: var(--ev-green);
    box-shadow: 0 0 0 3px rgba(46,125,50,.1);
}
#klevents .pja-filter-input { min-width: 180px; flex: 1; }
#klevents .pja-filter-month { width: 140px; }
#klevents .pja-btn-search {
    height: 36px;
    padding: 0 1.25rem;
    background: var(--ev-green);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .875rem;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .15s;
}
#klevents .pja-btn-search:hover { background: var(--ev-green-light); }
#klevents .pja-btn-clear {
    height: 36px;
    padding: 0 1rem;
    background: var(--ev-white);
    color: var(--ev-muted);
    border: 1.5px solid var(--ev-border);
    border-radius: 8px;
    font-size: .875rem;
    font-family: inherit;
    cursor: pointer;
    transition: background .15s;
}
#klevents .pja-btn-clear:hover { background: var(--ev-bg); color: var(--ev-navy); }
#klevents .pja-filter-limit {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .8rem;
    color: var(--ev-muted);
    margin-left: auto;
}

/* Event cards list */
#klevents .pja-events-list {
    display: flex;
    flex-direction: column;
    gap: .6rem;
}

/* Single event card */
#klevents .pja-event-card {
    background: var(--ev-white);
    border: 1px solid var(--ev-border);
    border-radius: var(--ev-radius);
    box-shadow: var(--ev-shadow-sm);
    display: flex;
    align-items: stretch;
    overflow: hidden;
    transition: box-shadow .15s, transform .12s;
    text-decoration: none;
    color: inherit;
    position: relative;
}
#klevents .pja-event-card:hover {
    box-shadow: var(--ev-shadow-md);
    transform: translateY(-2px);
    text-decoration: none;
}
#klevents .pja-event-card.featured {
    border-left: 3px solid #f59e0b;
}

/* Date badge — left column */
#klevents .pja-event-date {
    flex: 0 0 72px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: .85rem .5rem;
    background: var(--ev-green-pale);
    border-right: 1px solid var(--ev-border);
    text-align: center;
    line-height: 1.2;
    min-height: 80px;
}
#klevents .pja-event-date .pja-date-day {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--ev-green);
    line-height: 1;
}
#klevents .pja-event-date .pja-date-month {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--ev-green);
    margin-top: .15rem;
}
#klevents .pja-event-date .pja-date-time {
    font-size: .68rem;
    color: var(--ev-muted);
    margin-top: .25rem;
}

/* Event image */
#klevents .pja-event-image {
    flex: 0 0 80px;
    overflow: hidden;
}
#klevents .pja-event-image img {
    width: 80px;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Main content */
#klevents .pja-event-body {
    flex: 1;
    padding: .8rem 1rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
}
#klevents .pja-event-title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--ev-navy);
    margin-bottom: .3rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#klevents .pja-event-title a {
    color: inherit;
    text-decoration: none;
}
#klevents .pja-event-title a:hover { color: var(--ev-green); }
#klevents .pja-event-intro {
    font-size: .78rem;
    color: var(--ev-muted);
    line-height: 1.45;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    margin-bottom: .35rem;
}
#klevents .pja-event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem .75rem;
    font-size: .75rem;
    color: var(--ev-muted);
    align-items: center;
}
#klevents .pja-event-meta i {
    width: .9rem;
    text-align: center;
    color: var(--ev-green);
    font-size: .78rem;
}
#klevents .pja-event-meta a {
    color: var(--ev-muted);
    text-decoration: none;
}
#klevents .pja-event-meta a:hover { color: var(--ev-green); }

/* Right side — category + attendees */
#klevents .pja-event-aside {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    padding: .8rem 1rem;
    gap: .5rem;
}
#klevents .pja-cat-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 700;
    background: var(--ev-green-pale);
    color: var(--ev-green);
    border: 1px solid rgba(46,125,50,.18);
    text-decoration: none;
    white-space: nowrap;
}
#klevents .pja-cat-badge:hover {
    background: var(--ev-green);
    color: #fff;
    text-decoration: none;
}
#klevents .pja-attendees-badge {
    font-size: .72rem;
    color: var(--ev-muted);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: .3rem;
}
#klevents .pja-attendees-badge i { color: var(--ev-green); }

/* Featured star */
#klevents .pja-featured-badge {
    font-size: .7rem;
    color: #f59e0b;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: .25rem;
}

/* No events */
#klevents .pja-no-events {
    padding: 3rem 1.5rem;
    text-align: center;
    background: var(--ev-white);
    border: 1.5px dashed var(--ev-border);
    border-radius: var(--ev-radius);
    color: var(--ev-muted);
    font-size: .92rem;
}
#klevents .pja-no-events i {
    font-size: 2rem;
    display: block;
    margin-bottom: .75rem;
    color: var(--ev-border);
}

/* Pagination */
#klevents .pja-pagination {
    margin-top: 1.25rem;
    display: flex;
    justify-content: center;
}
#klevents .pja-pagination .pagination { gap: .25rem; display: flex; flex-wrap: wrap; justify-content: center; }
#klevents .pja-pagination .page-item .page-link {
    padding: .4rem .75rem;
    border: 1.5px solid var(--ev-border);
    border-radius: 8px !important;
    font-size: .83rem;
    font-weight: 600;
    color: var(--ev-navy);
    background: var(--ev-white);
    transition: all .15s;
}
#klevents .pja-pagination .page-item.active .page-link,
#klevents .pja-pagination .page-item .page-link:hover {
    background: var(--ev-green);
    border-color: var(--ev-green);
    color: #fff;
}

/* Responsive */
@media (max-width: 600px) {
    #klevents .pja-event-date { flex: 0 0 58px; }
    #klevents .pja-event-date .pja-date-day { font-size: 1.3rem; }
    #klevents .pja-event-aside { display: none; }
    #klevents .pja-cat-badge {
        display: inline-block;
        margin-top: .3rem;
    }
}
</style>

<?php if ($this->settings->get('global_show_filter', 1) || $this->settings->get('global_display', 1)): ?>
<div class="pja-filter-bar">
    <div class="pja-filter-row">
        <?php echo $this->lists['filter']; ?>
        <input type="text"
               name="filter_search"
               id="filter_search"
               class="pja-filter-input"
               placeholder="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>..."
               value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>"
               onchange="document.adminForm.submit();">
        <?php if ($this->settings->get('global_show_filter', 1)): ?>
            <input type="month"
                   name="filter_month"
                   id="filter_month"
                   class="pja-filter-input pja-filter-month"
                   placeholder="<?php echo Text::_('COM_PLANJEAGENDA_SEARCH_MONTH'); ?>"
                   value="<?php echo $this->lists['month'] ?? ''; ?>">
            <button class="pja-btn-search" type="submit">
                <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
                <?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>
            </button>
            <button class="pja-btn-clear" type="button"
                    onclick="document.getElementById('filter_search').value='';document.getElementById('filter_month').value='';this.form.submit();">
                <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        <?php endif; ?>
        <?php if ($this->settings->get('global_display', 1)): ?>
            <div class="pja-filter-limit">
                <span class="pja-filter-label"><?php echo Text::_('com_planjeagenda_DISPLAY_NUM'); ?></span>
                <?php echo $this->pagination?->getLimitBox(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (empty($this->rows)): ?>
    <div class="pja-no-events">
        <i class="far fa-calendar-times" aria-hidden="true"></i>
        <?php echo Text::_('com_planjeagenda_NO_EVENTS'); ?>
    </div>
<?php else: ?>
<div class="pja-events-list">
<?php foreach ($this->rows as $row):
    $eventUrl = Route::_(PlanjeagendaHelperRoute::getEventRoute($row->slug));
    $isFeatured = !empty($row->featured);

    // Parse date for badge
    $dateDay   = '';
    $dateMon   = '';
    $dateTime  = '';
    if (!empty($row->dates) && $row->dates !== '0000-00-00') {
        $ts      = strtotime($row->dates);
        $months  = ['jan','feb','mrt','apr','mei','jun','jul','aug','sep','okt','nov','dec'];
        $dateDay = date('j', $ts);
        $dateMon = $months[(int)date('n', $ts) - 1];
        if (!empty($row->times) && $row->times !== '00:00:00' && $this->jemsettings->showtime) {
            $dateTime = substr($row->times, 0, 5);
        }
    }
?>
    <div class="pja-event-card<?php echo $isFeatured ? ' featured' : ''; ?>"
         itemscope itemtype="https://schema.org/Event">
        <meta itemprop="url" content="<?php echo $eventUrl; ?>">
        <?php if (!empty($row->dates) && $row->dates !== '0000-00-00'): ?>
        <div class="pja-event-date" aria-label="<?php echo $dateDay . ' ' . $dateMon; ?>">
            <span class="pja-date-day"><?php echo $dateDay; ?></span>
            <span class="pja-date-month"><?php echo $dateMon; ?></span>
            <?php if ($dateTime): ?>
                <span class="pja-date-time"><?php echo $dateTime; ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($this->jemsettings->showeventimage == 1 && !empty($row->datimage)): ?>
        <div class="pja-event-image">
            <?php
            $dimage = PlanjeagendaImage::flyercreator($row->datimage, 'event');
            echo PlanjeagendaOutput::flyer($row, $dimage, 'event');
            ?>
        </div>
        <?php endif; ?>

        <div class="pja-event-body">
            <div class="pja-event-title" itemprop="name">
                <?php if ($this->jemsettings->showdetails == 1): ?>
                    <a href="<?php echo $eventUrl; ?>"><?php echo $this->escape($row->title); ?></a>
                <?php else: ?>
                    <?php echo $this->escape($row->title); ?>
                <?php endif; ?>
                <?php if ($isFeatured): ?>
                    <i class="fa fa-star" style="color:#f59e0b;font-size:.75em;margin-left:.3rem;" aria-hidden="true"></i>
                <?php endif; ?>
                <?php echo PlanjeagendaOutput::publishstateicon($row); ?>
                <?php if ($showiconsineventtitle): echo PlanjeagendaOutput::recurrenceicon($row); endif; ?>
            </div>

            <?php if ($this->params->get('show_introtext_events') == 1 && !empty($row->introtext)): ?>
                <div class="pja-event-intro">
                    <?php echo strip_tags($row->introtext); ?>
                </div>
            <?php endif; ?>

            <div class="pja-event-meta">
                <?php if ($this->jemsettings->showlocate == 1 && !empty($row->venue)): ?>
                <span>
                    <i class="fa fa-location-dot" aria-hidden="true"></i>
                    <?php if ($this->jemsettings->showlinkvenue == 1 && !empty($row->venueslug)): ?>
                        <a href="<?php echo Route::_(PlanjeagendaHelperRoute::getVenueRoute($row->venueslug)); ?>"
                           itemprop="location"><?php echo $this->escape($row->venue); ?></a>
                    <?php else: ?>
                        <span itemprop="location"><?php echo $this->escape($row->venue); ?></span>
                    <?php endif; ?>
                </span>
                <?php endif; ?>

                <?php if ($this->jemsettings->showcity == 1 && !empty($row->city)): ?>
                <span>
                    <i class="fa fa-city" aria-hidden="true"></i>
                    <?php echo $this->escape($row->city); ?>
                    <?php if ($this->jemsettings->showstate == 1 && !empty($row->state)): ?>
                        &mdash; <?php echo $this->escape($row->state); ?>
                    <?php endif; ?>
                </span>
                <?php elseif ($this->jemsettings->showstate == 1 && !empty($row->state)): ?>
                <span>
                    <i class="fa fa-map" aria-hidden="true"></i>
                    <?php echo $this->escape($row->state); ?>
                </span>
                <?php endif; ?>

                <?php /* Show category inline on mobile (aside is hidden) */ ?>
                <?php if ($this->jemsettings->showcat == 1): ?>
                <span class="pja-cat-inline-mobile" style="display:none;">
                    <?php
                    $cats = PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist);
                    foreach ($cats as $cat):
                    ?>
                        <a class="pja-cat-badge" href="#"><?php echo strip_tags($cat); ?></a>
                    <?php endforeach; ?>
                </span>
                <?php endif; ?>
            </div>

            <div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress" style="display:none;">
                <meta itemprop="streetAddress"   content="<?php echo $this->escape($row->street ?? ''); ?>">
                <meta itemprop="addressLocality" content="<?php echo $this->escape($row->city   ?? ''); ?>">
                <meta itemprop="addressRegion"   content="<?php echo $this->escape($row->state  ?? ''); ?>">
                <meta itemprop="postalCode"      content="<?php echo $this->escape($row->postalCode ?? ''); ?>">
            </div>
        </div>

        <div class="pja-event-aside">
            <?php if ($this->jemsettings->showcat == 1): ?>
                <?php
                $cats = PlanjeagendaOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist);
                foreach ($cats as $cat):
                ?>
                    <a class="pja-cat-badge" href="#"><?php echo strip_tags($cat); ?></a>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($this->jemsettings->showatte == 1 && isset($row->maxplaces)): ?>
                <span class="pja-attendees-badge">
                    <i class="fa fa-user-group" aria-hidden="true"></i>
                    <?php echo ($row->regCount ?? '0') . ' / ' . $this->escape($row->maxplaces); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php
// Pagination
if (!empty($this->pagination) && $this->pagination->pagesTotal > 1):
    echo '<div class="pja-pagination">' . $this->pagination->getPagesLinks() . '</div>';
endif;
?>
<?php echo PlanjeagendaOutput::lightbox(); ?>
