<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * Settings — Debug tab template
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$settings = $this->jemsettings;

// Determine log file path
$logPath = $settings->debug_logpath ?? 'tmp/klevents_debug.txt';
$logPath = ltrim($logPath, '/\\');
$logFile = JPATH_ROOT . DIRECTORY_SEPARATOR . $logPath;

// Read log content
$logContent   = '';
$logSize      = 0;
$logExists    = file_exists($logFile);
$debugEnabled = !empty($settings->debug_enabled);

if ($logExists) {
    $logSize    = filesize($logFile);
    $logContent = file_get_contents($logFile);
    // Show last 200 lines only
    $lines      = explode("\n", $logContent);
    if (count($lines) > 200) {
        $lines      = array_slice($lines, -200);
        $logContent = "... (showing last 200 lines) ...\n" . implode("\n", $lines);
    }
}
?>

<div class="row">
    <div class="col-12">

        <!-- Debug on/off setting -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><?php echo Text::_('com_planjeagenda_SETTINGS_DEBUG_LABEL'); ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?php foreach ($this->form->getFieldset('debug') as $field) : ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label; ?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="alert <?php echo $debugEnabled ? 'alert-warning' : 'alert-success'; ?>">
                            <?php if ($debugEnabled) : ?>
                                <strong><?php echo Text::_('com_planjeagenda_DEBUG_STATUS_ON'); ?></strong><br>
                                <?php echo Text::_('com_planjeagenda_DEBUG_STATUS_ON_DESC'); ?>
                            <?php else : ?>
                                <strong><?php echo Text::_('com_planjeagenda_DEBUG_STATUS_OFF'); ?></strong><br>
                                <?php echo Text::_('com_planjeagenda_DEBUG_STATUS_OFF_DESC'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log viewer -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <?php echo Text::_('com_planjeagenda_DEBUG_LOG_TITLE'); ?>
                    <small class="text-muted ms-2"><?php echo htmlspecialchars($logFile); ?></small>
                </h3>
                <div>
                    <?php if ($logExists && $logSize > 0) : ?>
                        <a href="index.php?option=com_planjeagenda&view=settings&klevents_clearlog=1"
                           class="btn btn-sm btn-warning me-2"
                           onclick="return confirm('<?php echo Text::_('com_planjeagenda_DEBUG_LOG_CLEAR_CONFIRM'); ?>')">
                            <?php echo Text::_('com_planjeagenda_DEBUG_LOG_CLEAR'); ?>
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-secondary"
                            onclick="document.getElementById('klevents-log').style.display = 
                                     document.getElementById('klevents-log').style.display === 'none' ? '' : 'none'">
                        <?php echo Text::_('com_planjeagenda_DEBUG_LOG_TOGGLE'); ?>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (!$logExists || $logSize === 0) : ?>
                    <div class="p-3 text-muted">
                        <?php echo Text::_('com_planjeagenda_DEBUG_LOG_EMPTY'); ?>
                    </div>
                <?php else : ?>
                    <div class="p-2 text-muted small">
                        <?php echo Text::sprintf('com_planjeagenda_DEBUG_LOG_SIZE', 
                            number_format($logSize / 1024, 1), 
                            count(explode("\n", $logContent))); ?>
                    </div>
                    <pre id="klevents-log"
                         style="max-height:400px; overflow-y:auto; background:#1e1e1e; color:#d4d4d4;
                                font-size:12px; padding:12px; margin:0; border-radius:0 0 4px 4px;"
                    ><?php echo htmlspecialchars($logContent); ?></pre>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
// Handle clear log action
$_app = \Joomla\CMS\Factory::getApplication();
if ($_app->input->get('klevents_clearlog', 0, 'int') === 1 && $logExists) {
    PlanjeagendaDebug::clear();
    $_app->enqueueMessage(Text::_('com_planjeagenda_DEBUG_LOG_CLEARED'), 'success');
    $_app->redirect('index.php?option=com_planjeagenda&view=settings&layout=debug');
}
