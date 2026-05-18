<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Button\FeaturedButton;
use Joomla\CMS\Button\PublishedButton;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\OutputHelper;
use Joomla\CMS\Layout\LayoutHelper;

$app       = Factory::getApplication();
$user      = $app->getIdentity();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canChange = $user->authorise('core.edit.state', 'com_planjeagenda');
$canEdit   = $user->authorise('core.edit');

$filterState = $this->state->get('filter_state', '');
$today       = date('Y-m-d');
$tomorrow    = date('Y-m-d', strtotime('+1 day'));

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');
?>

<form action="<?= Route::_('index.php?option=com_planjeagenda&view=events'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">

        <!-- Header + Toggle -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Activiteiten</h2>
            
            <div class="btn-group" role="group">
                <button type="button" id="btn-table" class="btn btn-sm btn-outline-primary active" onclick="toggleView('table')">
                    <span class="icon-list"></span> Tabel
                </button>
                <button type="button" id="btn-card" class="btn btn-sm btn-outline-primary" onclick="toggleView('card')">
                    <span class="icon-th"></span> Kaarten
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <select name="filter_municipality" class="form-select" onchange="this.form.submit();">
    <option value="">Selecteer gemeente</option>
    <?php echo HTMLHelper::_('select.options', $this->municipalities, 'value', 'text', $this->state->get('filter.municipality')); ?>
</select>
        
		<?php // Gebruik de standaard Joomla Search & Filter layout indien beschikbaar
		echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <!-- ==================== TABLE VIEW ==================== -->
        <div id="view-table">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle modern-table" id="eventList">
                    <thead class="table-light">
                        <tr>
                            <th width="1%" class="text-center"><input type="checkbox" onclick="Joomla.checkAll(this)"></th>
                            <th><?= HTMLHelper::_('grid.sort', 'com_planjeagenda_DATE', 'a.dates', $listDirn, $listOrder); ?></th>
                            <th><?= HTMLHelper::_('grid.sort', 'com_planjeagenda_STARTTIME_SHORT', 'a.times', $listDirn, $listOrder); ?></th>
                            <th><?= HTMLHelper::_('grid.sort', 'com_planjeagenda_EVENT_TITLE', 'a.title', $listDirn, $listOrder); ?></th>
                            <th><?= HTMLHelper::_('grid.sort', 'com_planjeagenda_VENUE', 'loc.venue', $listDirn, $listOrder); ?></th>
                            <th><?= Text::_('com_planjeagenda_CATEGORIES'); ?></th>
                            <th width="1%" class="text-center">Featured</th>
                            <th width="1%" class="text-center">Status</th>
                            <th>Auteur</th>
                            <th width="8%" class="text-center">Hits</th>
                            <th width="10%" class="text-center">Deelnemers</th>
                            <th width="1%" class="text-center">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->items ?? [] as $i => $row) : 
                        if ($row->published == -2 && $filterState !== '-2') continue;

                        // Datum Highlighting
                        $dateClass = '';
                        if (!empty($row->dates)) {
                            $eventDate = substr(trim($row->dates), 0, 10);
                            if ($eventDate < $today) {
                                $dateClass = 'text-muted';
                            } elseif ($eventDate === $today) {
                                $dateClass = 'text-success fw-bold';
                            } elseif ($eventDate === $tomorrow) {
                                $dateClass = 'text-warning fw-bold';
                            }
                        }
                    ?>
                        <tr>
                            <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $row->id); ?></td>
                            
                            <td class="text-nowrap <?= $dateClass; ?>">
                                <?php if ($row->checked_out) : ?>
                                    <?= HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'events.', true); ?>
                                <?php endif; ?>
                                <a href="<?= Route::_('index.php?option=com_planjeagenda&task=event.edit&id=' . (int)$row->id); ?>">
                                    <?= OutputHelper::formatShortDateTime($row->dates, null, $row->enddates ?? null, null, $this->jemsettings->showtime ?? true); ?>
                                </a>
                            </td>

                            <td><?= !empty($row->times) ? OutputHelper::formattime($row->times) : '—'; ?></td>
                            
                            <td>
                                <a href="<?= Route::_('index.php?option=com_planjeagenda&task=event.edit&id=' . (int)$row->id); ?>" class="fw-bold">
                                    <?= $this->escape($row->title); ?>
                                </a>
                                <?= OutputHelper::recurrenceicon($row); ?>
                            </td>

                            <td><?= $row->venue ? $this->escape($row->venue) : '<span class="text-muted">—</span>'; ?></td>
                            
                            <td>
                                <?php 
                                $cats = OutputHelper::getCategoryList($row->categories, $this->jemsettings->catlinklist ?? true, true);
                                foreach ((array)$cats as $cat) : ?>
                                    <span class="badge bg-secondary me-1"><?= $cat; ?></span>
                                <?php endforeach; ?>
                            </td>

                            <td class="text-center">
                                <?= (new FeaturedButton())->render((int)$row->featured, $i, ['task_prefix' => 'events.', 'disabled' => !$canChange, 'id' => (int)$row->id]); ?>
                            </td>
                            <td class="text-center">
                                <?= (new PublishedButton())->render((int)$row->published, $i, ['task_prefix' => 'events.', 'disabled' => !$canChange, 'id' => (int)$row->id], $row->publish_up ?? null, $row->publish_down ?? null); ?>
                            </td>

                            <td><?= $this->escape($row->author ?? ''); ?></td>
                            <td class="text-center"><?= (int)$row->hits; ?></td>
                            
                            <td class="text-center">
                            
                                                       <?php
                       
                                $linkreg     = 'index.php?option=com_planjeagenda&amp;view=attendees&amp;eventid='.$row->id;
                                $count = $row->regCount+$row->reserved;
                                if ($row->maxplaces)
                                {
                                    $count .= '/'.$row->maxplaces;
                                    if ($row->waitinglist && $row->waiting) {
                                        $count .= '+'.$row->waiting;
                                    }
                                }
                                if (!empty($row->unregCount)) {
                                    $count .= '-'.(int)$row->unregCount;
                                }
                                if (!empty($row->invited)) {
                                    $count .= ','.(int)$row->invited .'?';
                                }
                                ?>
                                <a href="<?php echo $linkreg; ?>" title="<?php echo Text::_('com_planjeagenda_EVENTS_MANAGEATTENDEES'); ?>">
                                    <?php echo $count; ?>
                                </a>
                            <?php 
                            
         
                               if (!empty($row->regCount) || !empty($row->reserved)) : 
                                    $total = (int)$row->regCount + (int)$row->reserved;
                                ?>
                                    <span class="badge bg-primary">👥 <?= $total; ?></span>
                                <?php else : ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= Route::_('index.php?option=com_planjeagenda&task=event.edit&id=' . (int)$row->id); ?>" title="Bewerken">
                                        <span class="icon-edit"></span>
                                    </a>
                                    <a class="btn btn-sm btn-outline-success" href="<?= Route::link('site', 'index.php?option=com_planjeagenda&view=event&id=' . (int)$row->id); ?>" target="_blank" title="Bekijk website">
                                        <span class="icon-eye"></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== CARD VIEW ==================== -->
        <div id="view-card" class="d-none">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
                <?php foreach ($this->items ?? [] as $row) : 
                    if ($row->published == -2 && $filterState !== '-2') continue;
                    $frontendLink = Route::link('site', 'index.php?option=com_planjeagenda&view=event&id=' . (int)$row->id);
                    $image = !empty($row->datimage) ? $row->datimage : '';
                ?>
                <div class="col">
                    <div class="card h-100 event-card border">
                        <?php if ($image) : ?>
                            <img src="<?= htmlspecialchars($image); ?>" class="card-img-top" alt="<?= $this->escape($row->title); ?>" style="height: 135px; object-fit: cover;">
                        <?php else : ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 135px;">
                                <span class="text-muted small">Geen afbeelding</span>
                            </div>
                        <?php endif; ?>

                        <div class="card-body p-3">
                            <h6 class="card-title mb-2"><?= $this->escape($row->title); ?></h6>
                            <p class="event-date small fw-semibold mb-1">
                                <?= OutputHelper::formatShortDateTime($row->dates, null, $row->enddates ?? null, null, true); ?>
                            </p>
                            <p class="small text-muted mb-2">
                                <?= !empty($row->times) ? OutputHelper::formattime($row->times) : '—'; ?>
                            </p>
                            <?php if ($row->venue) : ?>
                                <p class="small text-muted mb-2"><?= $this->escape($row->venue); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($row->regCount) || !empty($row->reserved)) : 
                                $total = (int)$row->regCount + (int)$row->reserved; ?>
                                <span class="badge bg-primary">👥 <?= $total; ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer bg-white p-2">
                            <div class="btn-group w-100">
                                <a href="<?= Route::_('index.php?option=com_planjeagenda&task=event.edit&id=' . (int)$row->id); ?>" class="btn btn-sm btn-outline-primary">Bewerken</a>
                                <a href="<?= $frontendLink; ?>" target="_blank" class="btn btn-sm btn-outline-success">Bekijk</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <?= $this->pagination->getPaginationLinks(); ?>
        </div>

    </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?= $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>" />
    <?= HTMLHelper::_('form.token'); ?>
</form>

<script>
function toggleView(view) {
    document.getElementById('view-table').classList.toggle('d-none', view !== 'table');
    document.getElementById('view-card').classList.toggle('d-none', view !== 'card');
    
    document.getElementById('btn-table').classList.toggle('active', view === 'table');
    document.getElementById('btn-card').classList.toggle('active', view === 'card');
    
    localStorage.setItem('planjeagenda_view', view);
}

document.addEventListener('DOMContentLoaded', function() {
    const saved = localStorage.getItem('planjeagenda_view') || 'table';
    toggleView(saved);
});
</script>