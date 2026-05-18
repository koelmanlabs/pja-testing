<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * Responsive eventslist outer template — modern action bar
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$app        = Factory::getApplication();
$user       = $app->getIdentity();
$isArchive  = $this->task === 'archive';
$icalUrl    = Route::_('index.php?option=com_planjeagenda&view=eventslist&format=ics');
$archiveUrl = $this->archive_link ?? '';
$canAdd     = $this->permissions->canAddEvent ?? false;
?>
<div id="klevents" class="jem_eventslist<?php echo $this->pageclass_sfx; ?>">

    <?php /* ── Action bar ───────────────────────────────────────────── */ ?>
    <div class="pja-ev-actionbar">

        <?php /* Left: archive toggle + result context */ ?>
        <div class="pja-ev-actionbar__left">
            <?php if ($isArchive): ?>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=eventslist&filter_reset=1'); ?>"
                   class="pja-ev-action-link pja-ev-action-link--active">
                    <svg width="13" height="13" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M32 32H480c17.7 0 32 14.3 32 32v32c0 17.7-14.3 32-32 32H32C14.3 128 0 113.7 0 96V64C0 46.3 14.3 32 32 32zm0 128H480V416c0 35.3-28.7 64-64 64H96c-35.3 0-64-28.7-64-64V160zm128 80c0 8.8 7.2 16 16 16H336c8.8 0 16-7.2 16-16s-7.2-16-16-16H176c-8.8 0-16 7.2-16 16z"/></svg>
                    <?php echo Text::_('com_planjeagenda_SHOW_EVENTS'); ?>
                </a>
            <?php else: ?>
                <?php if ($this->params->get('show_archived_events', 0)): ?>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=eventslist&task=archive&filter_reset=1'); ?>"
                   class="pja-ev-action-link">
                    <svg width="13" height="13" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M32 32H480c17.7 0 32 14.3 32 32v32c0 17.7-14.3 32-32 32H32C14.3 128 0 113.7 0 96V64C0 46.3 14.3 32 32 32zm0 128H480V416c0 35.3-28.7 64-64 64H96c-35.3 0-64-28.7-64-64V160zm128 80c0 8.8 7.2 16 16 16H336c8.8 0 16-7.2 16-16s-7.2-16-16-16H176c-8.8 0-16 7.2-16 16z"/></svg>
                    <?php echo Text::_('com_planjeagenda_SHOW_ARCHIVE'); ?>
                </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php /* Right: secondary actions */ ?>
        <div class="pja-ev-actionbar__right">

            <?php /* Print — subtle, only show if param enabled */ ?>
            <?php if ($this->params->get('show_print_button', 1)): ?>
            <a href="<?php echo $this->print_link ?? '#'; ?>"
               class="pja-ev-action-icon"
               title="<?php echo Text::_('JGLOBAL_PRINT'); ?>"
               onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');return false;"
               aria-label="<?php echo Text::_('JGLOBAL_PRINT'); ?>">
                <svg width="15" height="15" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M128 0C92.7 0 64 28.7 64 64v96h64V64H354.7L384 93.3V160h64V93.3c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0H128zM384 352v32 64H128V384 368 352H384zm64 32h32c17.7 0 32-14.3 32-32V256c0-35.3-28.7-64-64-64H64c-35.3 0-64 28.7-64 64v96c0 17.7 14.3 32 32 32H64v64c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V384z"/></svg>
            </a>
            <?php endif; ?>

            <?php /* iCal export — always available, subtle */ ?>
            <a href="<?php echo $icalUrl; ?>"
               class="pja-ev-action-icon"
               title="<?php echo Text::_('com_planjeagenda_ICAL'); ?>"
               aria-label="<?php echo Text::_('com_planjeagenda_ICAL'); ?>">
                <svg width="15" height="15" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/></svg>
                <span>iCal</span>
            </a>

            <?php /* Add activity — only for logged-in users with permission */ ?>
            <?php if ($canAdd): ?>
            <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=editevent&a_id=0'); ?>"
               class="pja-ev-action-add">
                <svg width="13" height="13" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
                <?php echo Text::_('TPL_PLANJEAGENDA_ADD_ACTIVITY'); ?>
            </a>
            <?php endif; ?>

        </div>
    </div>

    <style>
    #klevents .pja-ev-actionbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .85rem;
        gap: .5rem;
        flex-wrap: wrap;
    }
    #klevents .pja-ev-actionbar__left,
    #klevents .pja-ev-actionbar__right {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    #klevents .pja-ev-action-link {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .8rem;
        font-size: .78rem;
        font-weight: 600;
        color: #6b7280;
        border: 1px solid #e0e8f0;
        border-radius: 20px;
        text-decoration: none;
        transition: all .15s;
        background: #fff;
    }
    #klevents .pja-ev-action-link:hover {
        color: #1a2e5a;
        border-color: #1a2e5a;
        text-decoration: none;
    }
    #klevents .pja-ev-action-link--active {
        color: #2e7d32;
        border-color: #2e7d32;
        background: #e8f5e9;
    }
    #klevents .pja-ev-action-icon {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .3rem .7rem;
        font-size: .75rem;
        font-weight: 600;
        color: #9ca3af;
        border: 1px solid #e0e8f0;
        border-radius: 20px;
        text-decoration: none;
        background: #fff;
        transition: all .15s;
    }
    #klevents .pja-ev-action-icon:hover {
        color: #374151;
        border-color: #9ca3af;
        text-decoration: none;
    }
    #klevents .pja-ev-action-add {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .9rem;
        font-size: .8rem;
        font-weight: 700;
        color: #fff;
        background: #2e7d32;
        border: none;
        border-radius: 20px;
        text-decoration: none;
        transition: background .15s;
    }
    #klevents .pja-ev-action-add:hover {
        background: #4caf50;
        text-decoration: none;
    }
    #klevents .copyright { display: none; } /* hide "Powered by JEM" */
    </style>

    <?php if ($this->params->get('show_page_heading', 1) && $this->params->get('page_heading')): ?>
        <h1 class="pja-ev-heading"><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <?php if ($this->params->get('showintrotext') && $this->params->get('introtext')): ?>
        <div class="pja-ev-intro"><?php echo $this->params->get('introtext'); ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($this->action); ?>"
          method="post" name="adminForm" id="adminForm">

        <?php echo $this->loadTemplate('events_table'); ?>

        <input type="hidden" name="filter_order"     value="<?php echo $this->lists['order']; ?>">
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>">
        <input type="hidden" name="task"             value="<?php echo $this->task; ?>">
        <input type="hidden" name="view"             value="eventslist">
    </form>

    <?php if ($this->pagination && $this->pagination->pagesTotal > 1): ?>
        <div class="pja-ev-pagination">
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    <?php endif; ?>

</div>
