<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

if (!class_exists('PlanjeagendaHelper', false)) {
    require_once JPATH_SITE . '/components/com_planjeagenda/helpers/helper.php';
}


use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

$update = $this->updatedata;
$update->current          = $update->current ?? null;
$update->version          = $update->version ?? '?.?.?';
$update->installedversion = $update->installedversion ?? '0.0.0';
$update->failed           = $update->failed ?? true;

// Bepaal de statuskleur en icoon
$statusClass = 'success';
$statusIcon  = 'check-circle';
$statusText  = Text::_('COM_PLANJEAGENDA_STATUS_OK');

if ($update->failed) {
    $statusClass = 'warning';
    $statusIcon  = 'exclamation-triangle';
    $statusText  = Text::_('COM_PLANJEAGENDA_STATUS_OFFLINE');
} elseif ($update->current == -1) {
    $statusClass = 'danger';
    $statusIcon  = 'cloud-download';
    $statusText  = Text::_('COM_PLANJEAGENDA_STATUS_UPDATE_AVAILABLE');
}
?>

<div id="pja-dashboard" class="p-3">
    
    <!-- Header sectie met de Grote Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-start border-<?php echo $statusClass; ?> border-4">
                <div class="card-body d-flex align-items-center justify-content-between py-4">
                    <div class="d-flex align-items-center">
                        <div class="status-icon-wrapper me-4 bg-<?php echo $statusClass; ?>-light p-3 rounded-circle">
                             <span class="icon-<?php echo $statusIcon; ?> text-<?php echo $statusClass; ?> fs-1"></span>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold"><?php echo $statusText; ?></h2>
                            <p class="text-muted mb-0"><?php echo Text::_('COM_PLANJEAGENDA_LAST_CHECKED'); ?>: <?php echo date('d-m-Y H:i'); ?></p>
                        </div>
                    </div>
                    <?php if ($update->current == -1) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_installer&view=update'); ?>" class="btn btn-danger btn-lg shadow-sm">
                            <span class="icon-download me-2"></span><?php echo Text::_('COM_PLANJEAGENDA_INSTALL_NOW'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Linker kolom: Geïnstalleerde Versies -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3">
                    <span class="icon-list me-2 text-primary"></span><?php echo Text::_('COM_PLANJEAGENDA_INSTALLED_MODULES'); ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo Text::_('COM_PLANJEAGENDA_EXTENSION'); ?></th>
                                <th class="text-end"><?php echo Text::_('COM_PLANJEAGENDA_VERSION'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="icon-component me-2"></span> Component Core</td>
                                <td class="text-end"><span class="badge bg-light text-dark border"><?php echo $update->installedversion; ?></span></td>
                            </tr>
                            <tr>
                                <td><span class="icon-module me-2"></span> Agenda Module</td>
                                <td class="text-end"><span class="badge bg-light text-dark border"><?php echo \PlanjeagendaHelper::getExtensionVersion('mod_planjeagenda_events', 'module'); ?></span></td>
                            </tr>
                            <tr>
                                <td><span class="icon-power-off me-2"></span> Content Plugin</td>
                                <td class="text-end"><span class="badge bg-light text-dark border"><?php echo \PlanjeagendaHelper::getExtensionVersion('plg_content_planjeagenda', 'plugin'); ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rechter kolom: Release Informatie -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span><span class="icon-file-text me-2 text-primary"></span><?php echo Text::_('COM_PLANJEAGENDA_RELEASE_INFO'); ?></span>
                    <?php if (!$update->failed) : ?>
                        <span class="badge bg-primary">v<?php echo $update->version; ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($update->failed) : ?>
                        <div class="text-center py-5">
                            <span class="icon-info-circle text-muted fs-1 mb-3"></span>
                            <p class="text-muted"><?php echo Text::_('COM_PLANJEAGENDA_CONNECTION_FAILED_HINT'); ?></p>
                            <a href="https://www.koelmanlabs.nl" target="_blank" class="btn btn-outline-primary btn-sm">Bezoek KoelmanLabs.nl</a>
                        </div>
                    <?php else : ?>
                        <h6 class="fw-bold mb-3 font-monospace"><?php echo Text::_('COM_PLANJEAGENDA_CHANGELOG_SUMMARY'); ?>:</h6>
                        <div class="changelog-box p-3 bg-light rounded border mb-4" style="max-height: 250px; overflow-y: auto;">
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($update->changes as $change) : ?>
                                    <li class="mb-2"><span class="badge bg-success me-2">Nieuw</span> <?php echo $change; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="alert alert-secondary border-0 small">
                            <strong>Note:</strong> <?php echo implode(' ', $update->notes); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verborgen formulier voor Joomla toolbar acties -->
<form action="<?php echo Route::_('index.php?option=com_planjeagenda&view=updatecheck'); ?>" method="post" name="adminForm" id="adminForm">
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<style>
    /* Kleine extra styling voor dat 'premium' gevoel */
    .bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .status-icon-wrapper { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; }
    .border-dashed { border-style: dashed !important; }
    .font-monospace { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
</style>