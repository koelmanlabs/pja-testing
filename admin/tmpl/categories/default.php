<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
defined('_JEXEC') or die;


use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\OutputHelper;
use Joomla\CMS\Layout\LayoutHelper;

$user   = Factory::getApplication()->getIdentity();
$userId = $user->id;
$listOrder = $this->escape($this->state?->get('list.ordering') ?? 'a.lft');
$listDirn  = $this->escape($this->state?->get('list.direction') ?? 'asc');
$canOrder = $user->authorise('core.edit.state', 'com_planjeagenda.category');
$saveOrder = $listOrder == 'a.lft';
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');
?>
<form action="<?php echo Route::_('index.php?option=com_planjeagenda&view=categories'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <!-- Filter Bar -->
		<?php 
        // Gebruik de standaard Joomla Search & Filter layout indien beschikbaar
		echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); 
		?> <div class="clr"></div>
        <table class="table table-striped" id="articleList">
            <thead>
            <tr>
                <th style="width:1%" class="center">
                    <input type="checkbox" name="checkall-toggle" value=""
                           title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
                </th>
                <th>
                    <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.catname', $listDirn, $listOrder); ?>
                </th>
                <th style="width:5%" class="center" nowrap="nowrap">
                    <?php echo Text::_('com_planjeagenda_COLOR'); ?>
                </th>
                <th style="width:1%" class="center" nowrap="nowrap"><?php echo Text::_('com_planjeagenda_EVENTS'); ?></th>
                <th style="width:5%" class="center">
                    <?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                </th>
                <th style="width:1%" class="center nowrap">
                    <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                </th>
            </tr>
            </thead>

            <tbody>
            <?php
            $originalOrders = array();
            $countItems = count($this->items ?? []);

            foreach (($this->items ?? []) as $i => $item) :
                $ordering   = ($listOrder == 'a.lft');
                $canCreate  = $user->authorise('core.create');
                $orderkey   = array_search($item->id, $this->ordering[$item->parent_id]);
                $canEdit    = $user->authorise('core.edit');
                $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                $canEditOwn = $user->authorise('core.edit.own') && $item->created_user_id == $userId;
                $canChange  = $user->authorise('core.edit.state') && $canCheckin;
                $grouplink  = 'index.php?option=com_planjeagenda&amp;task=group.edit&amp;id=' . $item->groupid;

                if ($item->level > 0) {
                    $repeat = $item->level - 1;
                } else {
                    $repeat = 0;
                }
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="center">
                        <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td>
                        <?php echo str_repeat('<span class="gi">|&mdash;</span>', $repeat) ?>
                        <?php if ($item->checked_out) : ?>
                            <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin); ?>
                        <?php endif; ?>
                        <?php if ($canEdit || $canEditOwn) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_planjeagenda&task=category.edit&id=' . $item->id); ?>">
                                <?php echo $this->escape($item->catname); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($item->catname); ?>
                        <?php endif; ?>
                        <p class="smallsub" title="<?php echo $this->escape($item->path); ?>">
                            <?php echo str_repeat('<span class="gtr">|&mdash;</span>', $repeat) ?>
                            <?php if (empty($item->note)) : ?>
                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                            <?php else : ?>
                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
                            <?php endif; ?></p>
                    </td>
                    <td class="center">
                        <div class="colorpreview<?php echo ($item->color == '') ? ' transparent-color" title="transparent"' : '" style="background-color:' . htmlspecialchars($item->color, ENT_QUOTES, 'UTF-8') . '"' ?> aria-labelledby="
                             color-desc-<?php echo $item->id; ?>">
                        </div>
                        <div role="tooltip"
                             id="color-desc-<?php echo $item->id; ?>"><?php echo ($item->color == '') ? 'transparent' : htmlspecialchars($item->color, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </td>
                    <td class="center">
                        <?php echo $item->assignedevents; ?>
                    </td>
                    <td class="center">
                        <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'categories.', $canChange); ?>
                    </td>
                    <td class="center">
                        <span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
                            <?php echo (int)$item->id; ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="ms-auto mb-4 me-0">
            <?php echo (method_exists($this->pagination, 'getPaginationLinks') ? $this->pagination->getPaginationLinks(null) : $this->pagination?->getListFooter()); ?>
        </div>
    </div>

    <div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <input type="hidden" name="original_order_values" value="<?php echo implode(',', $originalOrders); ?>"/>

        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
