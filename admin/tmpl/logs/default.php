<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$listOrder = $this->escape($this->state?->get('list.ordering'));
$listDirn  = $this->escape($this->state?->get('list.direction'));
?>

<form action="<?php echo Route::_('index.php?option=com_planjeagenda&view=logs'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="15%"><?php echo HTMLHelper::_('searchtools.sort', 'COM_PLANJEAGENDA_DATE', 'created_at', $listDirn, $listOrder); ?></th>
                                <th width="10%"><?php echo HTMLHelper::_('searchtools.sort', 'COM_PLANJEAGENDA_LEVEL', 'level', $listDirn, $listOrder); ?></th>
                                <th width="20%"><?php echo HTMLHelper::_('searchtools.sort', 'COM_PLANJEAGENDA_LABEL', 'label', $listDirn, $listOrder); ?></th>
                                <th><?php echo Text::_('COM_PLANJEAGENDA_MESSAGE'); ?></th>
                                <th width="10%" class="text-end"><?php echo Text::_('COM_PLANJEAGENDA_PERFORMANCE'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($this->items)) : ?>
                            <?php foreach ($this->items as $i => $item) : 
                                // Bepaal kleur badge
                                $badgeClass = 'bg-info';
                                if ($item->level == 'error') $badgeClass = 'bg-danger';
                                if ($item->level == 'warning') $badgeClass = 'bg-warning text-dark';
                                ?>
                                <tr>
                                    <td class="small"><?php echo HTMLHelper::_('date', $item->created_at, 'd-m-Y H:i:s'); ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?> w-100"><?php echo strtoupper($item->level); ?></span></td>
                                    <td class="fw-bold"><?php echo $this->escape($item->label); ?></td>
                                    <td class="small">
                                        <div class="text-break"><?php echo nl2br($this->escape($item->message)); ?></div>
                                        <?php if ($item->file) : ?>
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                <span class="icon-file"></span> <?php echo $item->file; ?>:<?php echo $item->line; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end small text-muted">
                                        <?php echo $item->memory_kb; ?> KB<br>
                                        <?php echo $item->time_ms; ?> ms
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <span class="icon-info-circle fs-2"></span><br>Geen log-gegevens gevonden.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer px-3">
                    <?php echo $this->pagination?->getListFooter(); ?>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
