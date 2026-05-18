<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
   ->useScript('multiselect');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->id;
$listOrder = $this->escape($this->state?->get('list.ordering'));
$listDirn  = $this->escape($this->state?->get('list.direction'));
?>

<form action="<?php echo Route::_('index.php?option=com_planjeagenda&view=venues'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
		<?php 
		echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <div class="table-responsive">
            <table class="table table-striped" id="venueList">
                <thead>
                    <tr>
                        <th style="width:1%" class="text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>
                        <th scope="col" class="title">
                            <?php echo HTMLHelper::_('grid.sort', 'COM_PLANJEAGENDA_VENUE', 'a.venue', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" style="width:15%" class="d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('grid.sort', 'COM_PLANJEAGENDA_CITY', 'a.city', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" style="width:10%" class="text-center">
                            <?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" style="width:5%" class="text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('grid.sort', 'COM_PLANJEAGENDA_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (($this->items ?? []) as $i => $item) : 
                    $canEdit    = $user->authorise('core.edit', 'com_planjeagenda');
                    $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                    $canChange  = $user->authorise('core.edit.state', 'com_planjeagenda') && $canCheckin;
                ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="text-center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td>
                            <div class="name-container">
                                <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'venues.', $canCheckin); ?>
                                <?php endif; ?>
                                
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_planjeagenda&task=venue.edit&id=' . (int) $item->id); ?>" class="fw-bold">
                                        <?php echo $this->escape($item->venue); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->venue); ?>
                                <?php endif; ?>

                                <?php if (!empty($item->alias)) : ?>
                                    <div class="small text-muted" title="<?php echo Text::_('COM_PLANJEAGENDA_ALIAS'); ?>">
                                        <span class="icon-link small" aria-hidden="true"></span> <?php echo $this->escape($item->alias); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <span class="icon-location small text-muted" aria-hidden="true"></span>
                            <?php echo $item->city ? $this->escape($item->city) : Text::_('JNONE'); ?>
                        </td>
                        <td class="text-center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'venues.', $canChange); ?>
                        </td>
                        <td class="text-center d-none d-md-table-cell text-muted small">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php echo $this->pagination?->getListFooter(); ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>