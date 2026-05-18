<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$cat      = $this->category;
$catColor = !empty($cat->color) ? '#' . ltrim($cat->color, '#') : '#2e7d32';
$evUrl    = Route::_('index.php?option=com_planjeagenda&view=eventslist');
?>
<div id="klevents" class="jem_category<?php echo $this->pageclass_sfx; ?>">

    <!-- Back link -->
    <a href="<?php echo $evUrl; ?>" class="pja-back-link">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Terug naar activiteiten
    </a>

    <!-- Action bar -->
    <div class="pja-ev-actionbar" style="margin-bottom:1.25rem;">
        <div class="pja-ev-actionbar__left"></div>
        <div class="pja-ev-actionbar__right">
            <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=eventslist&format=raw&layout=ics'); ?>"
               class="pja-ev-action-icon" title="iCal">
                <svg width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/></svg>
                <span>iCal</span>
            </a>
        </div>
    </div>

    <!-- Hero image -->
    <?php if ($this->jemsettings->discatheader && !empty($cat->image)): ?>
    <div class="pja-cat-hero">
        <?php echo PlanjeagendaOutput::flyer($cat, $this->cimage, 'category'); ?>
    </div>
    <?php endif; ?>

    <!-- Category colour accent + title -->
    <div class="pja-accent-bar" style="background:<?php echo $catColor; ?>"></div>
    <span class="pja-colour-pill" style="background:<?php echo $catColor; ?>">Categorie</span>
    <h1 class="pja-page-title"><?php echo $this->escape($cat->title); ?></h1>

    <!-- Description -->
    <?php if (!empty($this->description)): ?>
    <div class="pja-cat-description"><?php echo $this->description; ?></div>
    <?php endif; ?>

    <!-- Subcategories -->
    <?php if ($this->showsubcats && $this->maxLevel != 0 && !empty($cat->id) && !empty($this->children[$cat->id])): ?>
    <h2 class="pja-section-heading">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z"/></svg>
        Subcategorieën
    </h2>
    <div class="pja-subcats">
        <?php foreach ($this->children[$cat->id] as $child):
            if (!$this->showemptysubcats && $child->getNumItems(true) < 1) continue; ?>
        <a href="<?php echo Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($child->slug ?? '', $this->task)); ?>"
           class="pja-subcat-pill">
            <?php echo $this->escape($child->title ?? ''); ?>
            <span class="pja-subcat-count"><?php echo (int)$child->getNumItems(true); ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Events in category -->
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
        <input type="hidden" name="view" value="category">
        <input type="hidden" name="task" value="<?php echo $this->task; ?>">
        <input type="hidden" name="id" value="<?php echo $cat->id; ?>">
    </form>

    <div class="pagination"><?php echo $this->pagination?->getPagesLinks(); ?></div>
</div>
<?php echo PlanjeagendaOutput::lightbox(); ?>
