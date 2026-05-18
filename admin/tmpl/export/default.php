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
use Joomla\CMS\Session\Session;

$token = Session::getFormToken();
$cats  = $this->categories ?? [];

$today    = date('Y-m-d');
$yearAgo  = date('Y-m-d', strtotime('-1 year'));
?>
<style>
.ex *{box-sizing:border-box}
.ex{max-width:900px}
.ex-tabs{display:flex;gap:4px;border-bottom:2px solid #dee2e6;margin-bottom:1.5rem}
.ex-tab{padding:9px 18px;font-size:13px;color:#6c757d;cursor:pointer;border-radius:6px 6px 0 0;border:1px solid transparent;border-bottom:none;margin-bottom:-2px;background:none;font-family:inherit}
.ex-tab:hover{background:#f8f9fa;color:#212529}
.ex-tab.active{background:#fff;border-color:#dee2e6;border-bottom-color:#fff;color:#212529;font-weight:600}
.ex-panel{display:none}
.ex-panel.active{display:block}
.ex-card{background:#fff;border:1px solid #dee2e6;border-radius:10px;overflow:hidden;margin-bottom:1rem}
.ex-card-head{padding:1rem 1.25rem;border-bottom:1px solid #f1f3f5;display:flex;align-items:center;gap:10px}
.ex-card-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.ex-card-title{font-size:14px;font-weight:600;color:#212529}
.ex-card-desc{font-size:12px;color:#6c757d;margin-top:2px}
.ex-card-body{padding:1.25rem}
.ex-filters{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:1.25rem}
.ex-field{display:flex;flex-direction:column;gap:5px}
.ex-field label{font-size:12px;font-weight:600;color:#495057;text-transform:uppercase;letter-spacing:.04em}
.ex-input{padding:7px 10px;border:1px solid #dee2e6;border-radius:6px;font-size:13px;color:#212529;width:100%;background:#fff}
.ex-input:focus{outline:none;border-color:#378ADD;box-shadow:0 0 0 3px rgba(55,138,221,.15)}
.ex-actions{display:flex;gap:8px;flex-wrap:wrap}
.ex-btn{padding:9px 18px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:background .15s}
.ex-btn-csv{background:#1D9E75;color:#fff}
.ex-btn-csv:hover{background:#0F6E56;color:#fff;text-decoration:none}
.ex-btn-json{background:#378ADD;color:#fff}
.ex-btn-json:hover{background:#1a5fa6;color:#fff;text-decoration:none}
.ex-btn-ical{background:#D85A30;color:#fff}
.ex-btn-ical:hover{background:#a83d1f;color:#fff;text-decoration:none}
.ex-btn-sec{background:#f8f9fa;color:#212529;border:1px solid #dee2e6}
.ex-btn-sec:hover{background:#e9ecef;text-decoration:none;color:#212529}
.ex-info{background:#f0f7ff;border:1px solid #c3ddf9;border-radius:8px;padding:.875rem 1rem;font-size:13px;color:#1a5fa6;margin-bottom:1rem;display:flex;gap:8px}
.ex-info i{flex-shrink:0;margin-top:1px}
.ex-formats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:1.5rem}
.ex-format{background:#fff;border:1px solid #dee2e6;border-radius:10px;padding:1.25rem;text-align:center}
.ex-format-icon{font-size:28px;margin-bottom:.5rem}
.ex-format-name{font-size:13px;font-weight:600;color:#212529}
.ex-format-desc{font-size:12px;color:#6c757d;margin-top:4px;line-height:1.5}
</style>

<div class="ex">

    <?php /* Formaat uitleg */ ?>
    <div class="ex-formats">
        <div class="ex-format">
            <div class="ex-format-icon">📊</div>
            <div class="ex-format-name">CSV</div>
            <div class="ex-format-desc">Geschikt voor Excel en andere spreadsheet programma's. Importeerbaar in de meeste systemen.</div>
        </div>
        <div class="ex-format">
            <div class="ex-format-icon">🗄️</div>
            <div class="ex-format-name">JSON</div>
            <div class="ex-format-desc">Volledige backup inclusief alle relaties. Gebruik dit voor migratie of herstel.</div>
        </div>
        <div class="ex-format">
            <div class="ex-format-icon">📅</div>
            <div class="ex-format-name">iCalendar</div>
            <div class="ex-format-desc">Import in Outlook, Google Calendar, Apple Calendar en andere agenda programma's.</div>
        </div>
    </div>

    <?php /* Tabs */ ?>
    <div class="ex-tabs">
        <button class="ex-tab active" onclick="exTab('events')">Evenementen</button>
        <button class="ex-tab" onclick="exTab('venues')">Locaties</button>
        <button class="ex-tab" onclick="exTab('categories')">Categorieën</button>
        <button class="ex-tab" onclick="exTab('backup')">Volledige backup</button>
    </div>

    <?php /* Evenementen panel */ ?>
    <div id="ex-events" class="ex-panel active">
        <div class="ex-info">
            <i class="icon-info" aria-hidden="true"></i>
            <div>Exporteer evenementen met locatie informatie. Gebruik de filters om een selectie te maken.</div>
        </div>

        <div class="ex-card">
            <div class="ex-card-head">
                <div class="ex-card-icon" style="background:#e1f5ee;color:#1D9E75"><i class="icon-filter" aria-hidden="true"></i></div>
                <div>
                    <div class="ex-card-title">Filters</div>
                    <div class="ex-card-desc">Laat leeg om alle evenementen te exporteren</div>
                </div>
            </div>
            <div class="ex-card-body">
                <div class="ex-filters">
                    <div class="ex-field">
                        <label>Datum van</label>
                        <input type="date" name="date_from" id="ev_date_from" class="ex-input" value="">
                    </div>
                    <div class="ex-field">
                        <label>Datum tot</label>
                        <input type="date" name="date_to" id="ev_date_to" class="ex-input" value="">
                    </div>
                    <div class="ex-field">
                        <label>Status</label>
                        <select id="ev_published" class="ex-input">
                            <option value="">Alle statussen</option>
                            <option value="1">Gepubliceerd</option>
                            <option value="0">Niet gepubliceerd</option>
                        </select>
                    </div>
                    <div class="ex-field">
                        <label>Categorie</label>
                        <select id="ev_catid" class="ex-input">
                            <option value="0">Alle categorieën</option>
                            <?php foreach ($cats as $cat) : ?>
                            <option value="<?php echo (int)$cat->id; ?>">
                                <?php echo htmlspecialchars($cat->title); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ex-actions">
                    <button class="ex-btn ex-btn-csv" onclick="doExport('exportEventsCsv', 'events')">
                        <i class="icon-download" aria-hidden="true"></i> Download CSV
                    </button>
                    <button class="ex-btn ex-btn-ical" onclick="doExport('exportIcal', 'events')">
                        <i class="icon-download" aria-hidden="true"></i> Download iCal (.ics)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php /* Locaties panel */ ?>
    <div id="ex-venues" class="ex-panel">
        <div class="ex-info">
            <i class="icon-info" aria-hidden="true"></i>
            <div>Exporteer alle locaties inclusief adres en GPS coördinaten.</div>
        </div>
        <div class="ex-card">
            <div class="ex-card-head">
                <div class="ex-card-icon" style="background:#e6f1fb;color:#378ADD"><i class="icon-filter" aria-hidden="true"></i></div>
                <div>
                    <div class="ex-card-title">Filters</div>
                    <div class="ex-card-desc">Laat leeg om alle locaties te exporteren</div>
                </div>
            </div>
            <div class="ex-card-body">
                <div class="ex-filters" style="grid-template-columns:1fr">
                    <div class="ex-field">
                        <label>Status</label>
                        <select id="ve_published" class="ex-input" style="max-width:250px">
                            <option value="">Alle statussen</option>
                            <option value="1">Gepubliceerd</option>
                            <option value="0">Niet gepubliceerd</option>
                        </select>
                    </div>
                </div>
                <div class="ex-actions">
                    <button class="ex-btn ex-btn-csv" onclick="doExport('exportVenuesCsv', 'venues')">
                        <i class="icon-download" aria-hidden="true"></i> Download CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php /* Categorieën panel */ ?>
    <div id="ex-categories" class="ex-panel">
        <div class="ex-info">
            <i class="icon-info" aria-hidden="true"></i>
            <div>Exporteer de categorieënstructuur inclusief kleuren en instellingen.</div>
        </div>
        <div class="ex-card">
            <div class="ex-card-body">
                <div class="ex-actions">
                    <button class="ex-btn ex-btn-csv" onclick="doExport('exportCategoriesCsv', 'categories')">
                        <i class="icon-download" aria-hidden="true"></i> Download CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php /* Backup panel */ ?>
    <div id="ex-backup" class="ex-panel">
        <div class="ex-info">
            <i class="icon-info" aria-hidden="true"></i>
            <div>Exporteer <strong>alle</strong> data als één JSON bestand. Bevat evenementen, locaties, categorieën en alle onderlinge relaties. Geschikt voor migratie naar een andere installatie.</div>
        </div>
        <div class="ex-card">
            <div class="ex-card-head">
                <div class="ex-card-icon" style="background:#fbeaf0;color:#D4537E"><i class="icon-database" aria-hidden="true"></i></div>
                <div>
                    <div class="ex-card-title">Volledige JSON backup</div>
                    <div class="ex-card-desc">Alle data inclusief relaties en metadata</div>
                </div>
            </div>
            <div class="ex-card-body">
                <div class="ex-filters">
                    <div class="ex-field">
                        <label>Datum van (optioneel)</label>
                        <input type="date" id="bk_date_from" class="ex-input" value="">
                    </div>
                    <div class="ex-field">
                        <label>Datum tot (optioneel)</label>
                        <input type="date" id="bk_date_to" class="ex-input" value="">
                    </div>
                </div>
                <div class="ex-actions">
                    <button class="ex-btn ex-btn-json" onclick="doExport('exportJson', 'backup')">
                        <i class="icon-download" aria-hidden="true"></i> Download JSON backup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function exTab(name) {
    document.querySelectorAll('.ex-tab').forEach((t, i) => {
        t.classList.toggle('active', ['events','venues','categories','backup'][i] === name);
    });
    document.querySelectorAll('.ex-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('ex-' + name).classList.add('active');
}

function doExport(task, context) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php';

    const fields = {
        'option': 'com_planjeagenda',
        'task':   'export.' + task,
        '<?php echo $token; ?>': '1',
    };

    // Context-specifieke filters
    if (context === 'events') {
        fields.date_from  = document.getElementById('ev_date_from').value;
        fields.date_to    = document.getElementById('ev_date_to').value;
        fields.published  = document.getElementById('ev_published').value;
        fields.catid      = document.getElementById('ev_catid').value;
    } else if (context === 'venues') {
        fields.published = document.getElementById('ve_published').value;
    } else if (context === 'backup') {
        fields.date_from = document.getElementById('bk_date_from').value;
        fields.date_to   = document.getElementById('bk_date_to').value;
    }

    Object.entries(fields).forEach(([k, v]) => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = k;
        input.value = v;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
