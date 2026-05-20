<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
   ->useScript('multiselect')
   ->addInlineStyle('
       .attendee-avatar { width: 42px; height: 42px; object-fit: cover; }
       .status-badge { font-size: 0.85rem; }
   ');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fa-solid fa-users"></i> <?= Text::_('COM_PLANJEAGENDA_ATTENDEES') ?></h2>
    <div>
        <?= HTMLHelper::_('bootstrap.tooltip'); ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="attendeeList">
                <thead>
                    <tr>
                        <td class="w-1 text-center">
                            <?= HTMLHelper::_('grid.checkall'); ?>
                        </td>
                        <th><?= Text::_('COM_PLANJEAGENDA_NAME') ?></th>
                        <th class="d-none d-md-table-cell"><?= Text::_('COM_PLANJEAGENDA_EMAIL') ?></th>
                        <th class="text-center"><?= Text::_('COM_PLANJEAGENDA_STATUS') ?></th>
                        <th class="d-none d-lg-table-cell"><?= Text::_('COM_PLANJEAGENDA_COMMENT') ?></th>
                        <th class="w-1 text-center"><?= Text::_('COM_PLANJEAGENDA_CHECKED_IN') ?></th>
                        <th class="w-1 text-end"><?= Text::_('JActions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item): ?>
                    <tr>
                        <td class="text-center">
                            <?= HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= $item->avatar ?? 'https://www.gravatar.com/avatar/' . md5(strtolower($item->email)) . '?s=42&d=mp' ?>" 
                                     class="attendee-avatar rounded-circle me-2" alt="">
                                <div>
                                    <strong><?= htmlspecialchars($item->name) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($item->email) ?></td>
                        <td class="text-center">
                            <?php
                            $statusClass = match($item->status) {
                                1 => 'bg-success',
                                2 => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $statusClass ?> status-badge">
                                <?= Text::_('COM_PLANJEAGENDA_ATTENDEE_STATUS_' . $item->status) ?>
                            </span>
                        </td>
                        <td class="d-none d-lg-table-cell text-truncate" style="max-width: 250px;">
                            <?= htmlspecialchars($item->comment ?? '') ?>
                        </td>
                        <td class="text-center">
                            <?php if ($item->checked_in): ?>
                                <span class="text-success"><i class="fa-solid fa-check"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= Route::_('index.php?option=com_planjeagenda&task=attendee.edit&id=' . $item->id) ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>