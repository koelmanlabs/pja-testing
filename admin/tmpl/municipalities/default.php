<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

?>
<form action="<?php echo Route::_('index.php?option=com_planjeagenda&view=municipalities'); ?>" method="post" id="adminForm" name="adminForm">
	<div class="table-responsive">
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="1%" class="text-center">
						<input type="checkbox" name="checkall-toggle" value="" title="Selecteer alles" onclick="Joomla.checkAll(this)" />
					</th>
					<th>
						Naam gemeente
					</th>
					<th width="1%" class="text-nowrap text-center">
						ID
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($this->items)) : ?>
					<?php foreach ($this->items as $i => $item) : ?>
						<?php $link = Route::_('index.php?option=com_planjeagenda&task=municipality.edit&id=' . (int) $item->id); ?>
						<tr>
							<td class="text-center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<a href="<?php echo $link; ?>">
									<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
								</a>
							</td>
							<td class="text-center">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="3" class="text-center">
							Geen gemeenten gevonden.
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>