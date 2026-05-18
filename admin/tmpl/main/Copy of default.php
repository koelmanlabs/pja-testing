<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$events   = $this->events   ?? new stdClass();
$venue    = $this->venue    ?? new stdClass();
$category = $this->category ?? new stdClass();
$update   = $this->updatedata ?? new stdClass();

$updateAvailable = !empty($update) && isset($update->current) && (int)$update->current === -1;
$canManage = $this->user->authorise('core.manage', 'com_planjeagenda');

$evTotal  = (int)($events->total       ?? 0);
$evPub    = (int)($events->published   ?? 0);
$evUnpub  = (int)($events->unpublished ?? 0);
$evArc    = (int)($events->archived    ?? 0);
$evTrash  = (int)($events->trashed     ?? 0);
$veTotal  = (int)($venue->total        ?? 0);
$catTotal = (int)($category->total     ?? 0);
?>
<style>
.pja *{box-sizing:border-box}
.pja{padding:1rem 0}
.pja-header{margin-bottom:1.25rem}
.pja-header h1{font-size:22px;font-weight:600;color:#212529}
.pja-header p{font-size:13px;color:#6c757d;margin-top:3px}
.pja-row1{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:1rem}
.pja-stat{background:#fff;border:1px solid #dee2e6;border-radius:10px;padding:1.125rem 1.25rem;display:flex;align-items:center;gap:14px;text-decoration:none;transition:box-shadow .15s;color:inherit}
.pja-stat:hover{box-shadow:0 2px 8px rgba(0,0,0,.08);text-decoration:none;color:inherit}
.pja-stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.ig{background:#e1f5ee;color:#1D9E75}
.ib{background:#e6f1fb;color:#378ADD}
.io{background:#faece7;color:#D85A30}
.ip{background:#fbeaf0;color:#D4537E}
.igr{background:#f1efe8;color:#5F5E5A}
.pja-stat-val{font-size:26px;font-weight:700;color:#212529;line-height:1}
.pja-stat-label{font-size:12px;color:#6c757d;margin-top:3px}
.pja-row2{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:1rem}
.pja-card{background:#fff;border:1px solid #dee2e6;border-radius:10px;overflow:hidden}
.pja-card-head{padding:.875rem 1.125rem;border-bottom:1px solid #f1f3f5;display:flex;align-items:center;justify-content:space-between}
.pja-card-title{font-size:13px;font-weight:600;color:#212529}
.pja-card-link{font-size:12px;color:#1D9E75;text-decoration:none}
.pja-card-link:hover{text-decoration:underline}
.pja-status-row{display:flex;justify-content:space-between;align-items:center;padding:8px 1.125rem;border-bottom:1px solid #f8f9fa}
.pja-status-row:last-child{border-bottom:none}
.pja-status-left{display:flex;align-items:center;gap:8px;font-size:13px;color:#6c757d}
.pja-dot{width:7px;height:7px;border-radius:50%}
.dp{background:#1D9E75}
.dc{background:#adb5bd}
.da{background:#EF9F27}
.dt{background:#E24B4A}
.pja-status-val{font-size:13px;font-weight:600;color:#212529}
.pja-actions{padding:.875rem 1.125rem;display:flex;flex-direction:column;gap:6px}
.pja-action{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:7px;background:#f8f9fa;border:1px solid #e9ecef;text-decoration:none;transition:background .15s;color:inherit}
.pja-action:hover{background:#e9ecef;text-decoration:none;color:inherit}
.pja-action-icon{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.pja-action span{font-size:13px;color:#212529;font-weight:500}
.pja-action small{font-size:11px;color:#6c757d;display:block;margin-top:1px}
.pja-action-arrow{margin-left:auto;font-size:16px;color:#adb5bd}
.pja-row3{display:flex;align-items:center;justify-content:space-between;gap:10px}
.pja-version{background:#fff;border:1px solid #dee2e6;border-radius:10px;padding:.875rem 1.25rem;display:flex;align-items:center;gap:10px;flex:1}
.pja-version-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.pja-version-text{font-size:13px;color:#6c757d}
.pja-version-text strong{color:#212529}
.pja-version-badge{font-size:11px;padding:3px 12px;border-radius:20px;font-weight:500;white-space:nowrap;margin-left:auto}
.pja-badge-ok{background:#e1f5ee;color:#0F6E56}
.pja-badge-warn{background:#faeeda;color:#854F0B}
.pja-new-btn{background:#1D9E75;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}
.pja-new-btn:hover{background:#0F6E56;color:#fff;text-decoration:none}
</style>

<div class="pja">

    <div class="pja-header">
        <h1>Plan Je Agenda</h1>
        <p><?php echo Text::_('com_planjeagenda_DASHBOARD_WELCOME'); ?></p>
    </div>

    <div class="pja-row1">
        <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=events'); ?>" class="pja-stat">
            <div class="pja-stat-icon ig"><i class="icon-calendar" aria-hidden="true"></i></div>
            <div><div class="pja-stat-val"><?php echo $evTotal; ?></div><div class="pja-stat-label"><?php echo Text::_('com_planjeagenda_EVENTS'); ?></div></div>
        </a>
        <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=venues'); ?>" class="pja-stat">
            <div class="pja-stat-icon ib"><i class="icon-map-marker" aria-hidden="true"></i></div>
            <div><div class="pja-stat-val"><?php echo $veTotal; ?></div><div class="pja-stat-label"><?php echo Text::_('com_planjeagenda_VENUES'); ?></div></div>
        </a>
        <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=categories'); ?>" class="pja-stat">
            <div class="pja-stat-icon io"><i class="icon-folder" aria-hidden="true"></i></div>
            <div><div class="pja-stat-val"><?php echo $catTotal; ?></div><div class="pja-stat-label"><?php echo Text::_('com_planjeagenda_CATEGORIES'); ?></div></div>
        </a>
        <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=groups'); ?>" class="pja-stat">
            <div class="pja-stat-icon ip"><i class="icon-users" aria-hidden="true"></i></div>
            <div><div class="pja-stat-val">-</div><div class="pja-stat-label"><?php echo Text::_('com_planjeagenda_GROUPS'); ?></div></div>
        </a>
    </div>

    <div class="pja-row2">

        <div class="pja-card">
            <div class="pja-card-head">
                <div class="pja-card-title"><?php echo Text::_('com_planjeagenda_MAIN_EVENT_STATS'); ?></div>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=events'); ?>" class="pja-card-link"><?php echo Text::_('JALL'); ?> &rarr;</a>
            </div>
            <div class="pja-status-row">
                <div class="pja-status-left"><div class="pja-dot dp"></div><?php echo Text::_('JPUBLISHED'); ?></div>
                <span class="pja-status-val"><?php echo $evPub; ?></span>
            </div>
            <div class="pja-status-row">
                <div class="pja-status-left"><div class="pja-dot dc"></div><?php echo Text::_('JUNPUBLISHED'); ?></div>
                <span class="pja-status-val"><?php echo $evUnpub; ?></span>
            </div>
            <div class="pja-status-row">
                <div class="pja-status-left"><div class="pja-dot da"></div><?php echo Text::_('JARCHIVED'); ?></div>
                <span class="pja-status-val"><?php echo $evArc; ?></span>
            </div>
            <div class="pja-status-row">
                <div class="pja-status-left"><div class="pja-dot dt"></div><?php echo Text::_('JTRASHED'); ?></div>
                <span class="pja-status-val"><?php echo $evTrash; ?></span>
            </div>
        </div>

        <div class="pja-card">
            <div class="pja-card-head">
                <div class="pja-card-title"><?php echo Text::_('com_planjeagenda_QUICK_ADD'); ?></div>
            </div>
            <div class="pja-actions">
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&task=event.add'); ?>" class="pja-action">
                    <div class="pja-action-icon ig"><i class="icon-plus" aria-hidden="true"></i></div>
                    <div><span><?php echo Text::_('com_planjeagenda_ADD_EVENT'); ?></span><small><?php echo Text::_('com_planjeagenda_ADD_EVENT_DESC'); ?></small></div>
                    <span class="pja-action-arrow">&rsaquo;</span>
                </a>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&task=venue.add'); ?>" class="pja-action">
                    <div class="pja-action-icon ib"><i class="icon-plus" aria-hidden="true"></i></div>
                    <div><span><?php echo Text::_('com_planjeagenda_ADD_VENUE'); ?></span><small><?php echo Text::_('com_planjeagenda_ADD_VENUE_DESC'); ?></small></div>
                    <span class="pja-action-arrow">&rsaquo;</span>
                </a>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&task=category.add'); ?>" class="pja-action">
                    <div class="pja-action-icon io"><i class="icon-plus" aria-hidden="true"></i></div>
                    <div><span><?php echo Text::_('com_planjeagenda_ADD_CATEGORY'); ?></span><small><?php echo Text::_('com_planjeagenda_ADD_CATEGORY_DESC'); ?></small></div>
                    <span class="pja-action-arrow">&rsaquo;</span>
                </a>
            </div>
        </div>

        <div class="pja-card">
            <div class="pja-card-head">
                <div class="pja-card-title"><?php echo Text::_('com_planjeagenda_MANAGE'); ?></div>
            </div>
            <div class="pja-actions">
                <?php if ($canManage) : ?>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=settings'); ?>" class="pja-action">
                    <div class="pja-action-icon igr"><i class="icon-cog" aria-hidden="true"></i></div>
                    <div><span><?php echo Text::_('com_planjeagenda_SETTINGS_TITLE'); ?></span><small><?php echo Text::_('com_planjeagenda_SETTINGS_DESC'); ?></small></div>
                    <span class="pja-action-arrow">&rsaquo;</span>
                </a>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=import'); ?>" class="pja-action">
                    <div class="pja-action-icon igr"><i class="icon-upload" aria-hidden="true"></i></div>
                    <div><span><?php echo Text::_('com_planjeagenda_IMPORT_EXPORT'); ?></span><small><?php echo Text::_('com_planjeagenda_IMPORT_EXPORT_DESC'); ?></small></div>
                    <span class="pja-action-arrow">&rsaquo;</span>
                </a>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=cssmanager'); ?>" class="pja-action">
                    <div class="pja-action-icon igr"><i class="icon-eye" aria-hidden="true"></i></div>
                    <div><span><?php echo Text::_('com_planjeagenda_CSSMANAGER_TITLE'); ?></span><small><?php echo Text::_('com_planjeagenda_CSSMANAGER_DESC'); ?></small></div>
                    <span class="pja-action-arrow">&rsaquo;</span>
                </a>
                <?php else : ?>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=help'); ?>" class="pja-action">
                    <div class="pja-action-icon igr"><i class="icon-question-sign" aria-hidden="true"></i></div>
                    <div><span><?php echo Text::_('com_planjeagenda_HELP'); ?></span></div>
                    <span class="pja-action-arrow">&rsaquo;</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="pja-row3">
        <div class="pja-version">
            <div class="pja-version-dot" style="background:<?php echo $updateAvailable ? '#EF9F27' : '#1D9E75'; ?>"></div>
            <div class="pja-version-text"><strong>Plan Je Agenda</strong> &middot; Koelman Labs &middot; Joomla 6</div>
            <span class="pja-version-badge <?php echo $updateAvailable ? 'pja-badge-warn' : 'pja-badge-ok'; ?>">
                <?php echo $updateAvailable ? Text::_('com_planjeagenda_UPDATE_AVAILABLE') : Text::_('com_planjeagenda_VERSION_CURRENT'); ?>
            </span>
        </div>
        <a href="<?php echo Route::_('index.php?option=com_planjeagenda&task=event.add'); ?>" class="pja-new-btn">
            <i class="icon-plus" aria-hidden="true"></i>
            <?php echo Text::_('com_planjeagenda_ADD_EVENT'); ?>
        </a>
    </div>

</div>
