<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$filters = $this->filters ?? ['tab'=>'all','level'=>'','component'=>'','search'=>''];
$tab     = $filters['tab'] ?? 'all';
$counts  = $this->counts ?? ['debug'=>0,'info'=>0,'warning'=>0,'error'=>0,'total'=>0];

$levelColors = [
    'debug'   => '#6c757d',
    'info'    => '#378ADD',
    'warning' => '#EF9F27',
    'error'   => '#E24B4A',
];
$levelBg = [
    'debug'   => '#f8f9fa',
    'info'    => '#e6f1fb',
    'warning' => '#faeeda',
    'error'   => '#fbeaea',
];
?>
<style>
.dl *{box-sizing:border-box}
.dl-tabs{display:flex;gap:4px;margin-bottom:1rem;border-bottom:2px solid #dee2e6;padding-bottom:0}
.dl-tab{padding:8px 16px;font-size:13px;color:#6c757d;cursor:pointer;border-radius:6px 6px 0 0;border:1px solid transparent;border-bottom:none;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:-2px}
.dl-tab:hover{background:#f8f9fa;color:#212529;text-decoration:none}
.dl-tab.active{background:#fff;border-color:#dee2e6;border-bottom-color:#fff;color:#212529;font-weight:600}
.dl-badge{font-size:11px;padding:2px 7px;border-radius:10px;font-weight:500}
.dl-badge-debug{background:#e9ecef;color:#495057}
.dl-badge-info{background:#e6f1fb;color:#1a5fa6}
.dl-badge-warning{background:#faeeda;color:#854F0B}
.dl-badge-error{background:#fbeaea;color:#9a1e1e}
.dl-badge-total{background:#212529;color:#fff}

.dl-bar{display:flex;gap:8px;align-items:center;margin-bottom:1rem;flex-wrap:wrap}
.dl-filter{padding:6px 10px;border:1px solid #dee2e6;border-radius:6px;font-size:13px;color:#212529;background:#fff}
.dl-search{padding:6px 12px;border:1px solid #dee2e6;border-radius:6px;font-size:13px;flex:1;min-width:200px}
.dl-btn{padding:7px 14px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;border:none;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.dl-btn-apply{background:#378ADD;color:#fff}
.dl-btn-apply:hover{background:#1a5fa6;color:#fff;text-decoration:none}
.dl-btn-reset{background:#f8f9fa;color:#212529;border:1px solid #dee2e6}
.dl-btn-reset:hover{background:#e9ecef;text-decoration:none;color:#212529}
.dl-btn-danger{background:#E24B4A;color:#fff}
.dl-btn-danger:hover{background:#b91c1b;color:#fff;text-decoration:none}

.dl-table{width:100%;border-collapse:collapse;font-size:13px;background:#fff;border:1px solid #dee2e6;border-radius:8px;overflow:hidden}
.dl-table th{padding:9px 12px;background:#f8f9fa;color:#495057;font-weight:600;text-align:left;border-bottom:2px solid #dee2e6;font-size:12px;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
.dl-table td{padding:8px 12px;border-bottom:1px solid #f1f3f5;vertical-align:top}
.dl-table tr:last-child td{border-bottom:none}
.dl-table tr:hover td{background:#f8f9fa}
.dl-level{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;text-transform:uppercase}
.dl-label{font-weight:500;color:#212529}
.dl-message{color:#495057;font-size:12px;margin-top:3px;font-family:monospace;word-break:break-all;white-space:pre-wrap}
.dl-query{background:#1e1e1e;color:#d4d4d4;padding:8px;border-radius:4px;font-size:11px;font-family:monospace;margin-top:4px;word-break:break-all;white-space:pre-wrap;max-height:150px;overflow-y:auto}
.dl-meta{font-size:11px;color:#adb5bd;white-space:nowrap}
.dl-empty{text-align:center;padding:3rem;color:#6c757d;font-size:14px}
.dl-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-bottom:1rem}
.dl-stat{background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:.75rem 1rem;text-align:center}
.dl-stat-val{font-size:22px;font-weight:700;color:#212529}
.dl-stat-label{font-size:11px;color:#6c757d;margin-top:2px}
</style>

<div class="dl">

    <?php /* Stats bovenin */ ?>
    <div class="dl-stats">
        <div class="dl-stat">
            <div class="dl-stat-val"><?php echo $counts['total']; ?></div>
            <div class="dl-stat-label">Totaal</div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-val" style="color:#6c757d"><?php echo $counts['debug']; ?></div>
            <div class="dl-stat-label">Debug</div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-val" style="color:#378ADD"><?php echo $counts['info']; ?></div>
            <div class="dl-stat-label">Info</div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-val" style="color:#EF9F27"><?php echo $counts['warning']; ?></div>
            <div class="dl-stat-label">Waarschuwing</div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-val" style="color:#E24B4A"><?php echo $counts['error']; ?></div>
            <div class="dl-stat-label">Fout</div>
        </div>
    </div>

    <?php /* Tabs */ ?>
    <div class="dl-tabs">
        <a href="?option=com_planjeagenda&view=debuglog&tab=all" class="dl-tab <?php echo $tab==='all'?'active':''; ?>">
            Alle entries <span class="dl-badge dl-badge-total"><?php echo $counts['total']; ?></span>
        </a>
        <a href="?option=com_planjeagenda&view=debuglog&tab=queries" class="dl-tab <?php echo $tab==='queries'?'active':''; ?>">
            Queries
        </a>
        <a href="?option=com_planjeagenda&view=debuglog&filter_level=warning" class="dl-tab <?php echo $filters['level']==='warning'?'active':''; ?>">
            Waarschuwingen <span class="dl-badge dl-badge-warning"><?php echo $counts['warning']; ?></span>
        </a>
        <a href="?option=com_planjeagenda&view=debuglog&filter_level=error" class="dl-tab <?php echo $filters['level']==='error'?'active':''; ?>">
            Fouten <span class="dl-badge dl-badge-error"><?php echo $counts['error']; ?></span>
        </a>
        <a href="?option=com_planjeagenda&view=debuglog&action=clear"
           class="dl-tab" style="margin-left:auto;color:#E24B4A"
           onclick="return confirm('Log tabel leegmaken?')">
            <i class="icon-trash" aria-hidden="true"></i> Leegmaken
        </a>
    </div>

    <?php /* Filter balk */ ?>
    <form method="get" action="index.php">
        <input type="hidden" name="option" value="com_planjeagenda">
        <input type="hidden" name="view" value="debuglog">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
        <div class="dl-bar">
            <select name="filter_level" class="dl-filter">
                <option value="">Alle levels</option>
                <?php foreach (['debug','info','warning','error'] as $l) : ?>
                <option value="<?php echo $l; ?>" <?php echo $filters['level']===$l?'selected':''; ?>>
                    <?php echo ucfirst($l); ?>
                </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($this->components)) : ?>
            <select name="filter_component" class="dl-filter">
                <option value="">Alle componenten</option>
                <?php foreach ($this->components as $comp) : ?>
                <option value="<?php echo htmlspecialchars($comp); ?>"
                    <?php echo $filters['component']===$comp?'selected':''; ?>>
                    <?php echo htmlspecialchars($comp); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <input type="text" name="filter_search" class="dl-search"
                   placeholder="Zoeken in label, bericht, bestand..."
                   value="<?php echo htmlspecialchars($filters['search']); ?>">

            <button type="submit" class="dl-btn dl-btn-apply">
                <i class="icon-search" aria-hidden="true"></i> Zoeken
            </button>
            <a href="?option=com_planjeagenda&view=debuglog" class="dl-btn dl-btn-reset">
                Reset
            </a>
        </div>
    </form>

    <?php /* Log tabel */ ?>
    <?php if (empty($this->items)) : ?>
        <div class="dl-empty">
            <i class="icon-info-circle" aria-hidden="true" style="font-size:32px;display:block;margin-bottom:.5rem;color:#dee2e6"></i>
            Geen log entries gevonden.
        </div>
    <?php else : ?>
    <table class="dl-table">
        <thead>
            <tr>
                <th style="width:70px">Level</th>
                <th>Label / Bericht</th>
                <th style="width:110px">Component</th>
                <th style="width:140px">Bestand</th>
                <th style="width:80px">Geheugen</th>
                <th style="width:70px">+ms</th>
                <th style="width:130px">Tijdstip</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $item) : ?>
            <?php
            $bg    = $levelBg[$item->level]    ?? '#fff';
            $color = $levelColors[$item->level] ?? '#212529';
            ?>
            <tr style="background:<?php echo $bg; ?>">
                <td>
                    <span class="dl-level" style="background:<?php echo $color; ?>20;color:<?php echo $color; ?>">
                        <?php echo htmlspecialchars($item->level); ?>
                    </span>
                </td>
                <td>
                    <div class="dl-label"><?php echo htmlspecialchars($item->label); ?></div>
                    <?php if (!empty($item->message)) : ?>
                        <div class="dl-message"><?php echo htmlspecialchars($item->message); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($item->query)) : ?>
                        <div class="dl-query"><?php echo htmlspecialchars($item->query); ?></div>
                    <?php endif; ?>
                </td>
                <td class="dl-meta"><?php echo htmlspecialchars($item->component); ?></td>
                <td class="dl-meta">
                    <?php echo htmlspecialchars($item->file); ?>
                    <?php if ($item->line > 0) : ?>
                        <span style="color:#dee2e6">:</span><?php echo (int)$item->line; ?>
                    <?php endif; ?>
                </td>
                <td class="dl-meta"><?php echo number_format($item->memory_kb); ?> KB</td>
                <td class="dl-meta"><?php echo round($item->time_ms, 1); ?>ms</td>
                <td class="dl-meta"><?php echo date('H:i:s', strtotime($item->created_at)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</div>
