<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div id="klevents" class="jem_categories<?php echo $this->pageclass_sfx; ?>">

    <div class="pja-ev-actionbar" style="margin-bottom:1.5rem;">
        <div class="pja-ev-actionbar__left">
            <h1 class="pja-page-title" style="margin:0;">
                <?php echo $this->escape($this->params->get('page_heading', Text::_('com_planjeagenda_CATEGORIES'))); ?>
            </h1>
        </div>
        <div class="pja-ev-actionbar__right"></div>
    </div>

    <?php foreach (($this->rows ?? []) as $row):
        $color = !empty($row->color) ? '#' . ltrim($row->color, '#') : '#2e7d32';
        $hasAccess = $row->user_has_access_category ?? true;
    ?>
    <div class="pja-cat-list-item">
        <div class="pja-cat-list-header">
            <div class="pja-cat-list-icon" style="background:<?php echo $color; ?>;" aria-hidden="true">
                <?php if (!empty($row->icon)): ?>
                <span style="font-size:1.1rem;"><?php echo $this->escape($row->icon); ?></span>
                <?php else: ?>
                <svg width="18" height="18" viewBox="0 0 448 512" fill="currentColor"><path d="M0 80V229.5c0 17 6.7 33.3 18.7 45.3l176 176c25 25 65.5 25 90.5 0L418.7 317.3c25-25 25-65.5 0-90.5l-176-176c-12-12-28.3-18.7-45.3-18.7H48C21.5 32 0 53.5 0 80zm112 32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>
                <?php endif; ?>
            </div>
            <?php if ($hasAccess): ?>
            <a href="<?php echo Route::_($row->linktarget); ?>" class="pja-cat-list-title">
                <?php echo $this->escape($row->catname); ?>
            </a>
            <?php else: ?>
            <span class="pja-cat-list-title" style="color:#9ca3af;">
                <?php echo $this->escape($row->catname); ?>
                <svg width="11" height="11" viewBox="0 0 448 512" fill="#9ca3af" style="vertical-align:middle;"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
            </span>
            <?php endif; ?>
            <span class="pja-cat-list-count">
                <?php echo (int)($row->assignedevents ?? 0); ?> activiteiten
            </span>
        </div>

        <?php if ($hasAccess): ?>
        <div class="pja-cat-list-body">
            <?php if (!empty($row->description)): ?>
            <div class="pja-cat-list-desc"><?php echo $row->description; ?></div>
            <?php endif; ?>

            <?php if (!empty($row->subcats)): ?>
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.5rem;">
                <?php foreach ($row->subcats as $i => $sub): ?>
                <a href="<?php echo Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($sub->slug, $this->task)); ?>"
                   style="display:inline-flex;align-items:center;padding:.2rem .6rem;background:#f0f4ff;border:1px solid #c7d7f5;border-radius:16px;font-size:.75rem;font-weight:600;color:#3a5cbf;text-decoration:none;">
                    <?php echo $this->escape($sub->catname); ?>
                    <span style="margin-left:.3rem;opacity:.7;">(<?php echo (int)($sub->assignedevents ?? 0); ?>)</span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (($this->params->get('detcat_nr', 0) > 0) && !empty($this->catrow)): ?>
                <?php $this->catrow = $row; ?>
                <?php echo $this->loadTemplate('jem_eventslist'); ?>
            <?php endif; ?>
        </div>

        <div class="pja-cat-list-footer">
            <span style="font-size:.8rem;color:#9ca3af;">
                <?php echo (int)($row->assignedevents ?? 0); ?>
                <?php echo (int)($row->assignedevents ?? 0) === 1 ? Text::_('com_planjeagenda_EVENT') : Text::_('com_planjeagenda_EVENTS'); ?>
            </span>
            <a href="<?php echo Route::_($row->linktarget); ?>" class="pja-show-all-btn">
                Bekijk alles
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="pagination"><?php echo $this->pagination?->getPagesLinks(); ?></div>
</div>
