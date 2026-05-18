<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

$token = Session::getFormToken();
?>
<style>
.im *{box-sizing:border-box}
.im{max-width:860px}

/* Stap indicator */
.im-steps{display:flex;align-items:center;margin-bottom:2rem}
.im-step{display:flex;align-items:center;gap:8px;flex:1}
.im-step-num{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;transition:all .3s}
.im-step-num.done{background:#1D9E75;color:#fff}
.im-step-num.active{background:#212529;color:#fff}
.im-step-num.pending{background:#e9ecef;color:#adb5bd}
.im-step-label{font-size:13px;color:#6c757d}
.im-step-label.active{color:#212529;font-weight:600}
.im-step-line{flex:1;height:2px;background:#dee2e6;margin:0 8px}
.im-step-line.done{background:#1D9E75}

/* Panels */
.im-panel{display:none}
.im-panel.active{display:block}

/* Cards */
.im-card{background:#fff;border:1px solid #dee2e6;border-radius:10px;overflow:hidden;margin-bottom:1rem}
.im-card-head{padding:1rem 1.25rem;border-bottom:1px solid #f1f3f5}
.im-card-title{font-size:14px;font-weight:600;color:#212529}
.im-card-desc{font-size:12px;color:#6c757d;margin-top:2px}
.im-card-body{padding:1.25rem}

/* Drag & Drop zone */
.im-dropzone{border:2px dashed #dee2e6;border-radius:10px;padding:2.5rem;text-align:center;cursor:pointer;transition:all .2s;background:#fafafa}
.im-dropzone:hover,.im-dropzone.drag-over{border-color:#1D9E75;background:#f0faf6}
.im-dropzone-icon{font-size:40px;color:#dee2e6;margin-bottom:.75rem}
.im-dropzone-title{font-size:15px;font-weight:600;color:#212529;margin-bottom:4px}
.im-dropzone-sub{font-size:13px;color:#6c757d}
.im-dropzone-formats{font-size:12px;color:#adb5bd;margin-top:.5rem}
.im-file-selected{background:#f0faf6;border-color:#1D9E75}
.im-file-selected .im-dropzone-icon{color:#1D9E75}

/* Fields */
.im-field{display:flex;flex-direction:column;gap:5px;margin-bottom:1rem}
.im-field label{font-size:12px;font-weight:600;color:#495057;text-transform:uppercase;letter-spacing:.04em}
.im-input,.im-select{padding:7px 10px;border:1px solid #dee2e6;border-radius:6px;font-size:13px;color:#212529;width:100%;background:#fff}
.im-input:focus,.im-select:focus{outline:none;border-color:#378ADD;box-shadow:0 0 0 3px rgba(55,138,221,.15)}

/* Type tabs */
.im-type-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:1.25rem}
.im-type-btn{padding:.875rem;border:2px solid #dee2e6;border-radius:8px;background:#fff;cursor:pointer;text-align:center;transition:all .15s}
.im-type-btn:hover{border-color:#adb5bd}
.im-type-btn.active{border-color:#1D9E75;background:#f0faf6}
.im-type-btn-icon{font-size:24px;margin-bottom:4px}
.im-type-btn-label{font-size:13px;font-weight:600;color:#212529}

/* Column mapping */
.im-mapping-table{width:100%;border-collapse:collapse}
.im-mapping-table th{padding:8px 12px;background:#f8f9fa;font-size:12px;color:#495057;font-weight:600;text-align:left;border-bottom:2px solid #dee2e6}
.im-mapping-table td{padding:8px 12px;border-bottom:1px solid #f1f3f5;font-size:13px}
.im-mapping-select{width:100%;padding:5px 8px;border:1px solid #dee2e6;border-radius:5px;font-size:12px}

/* Preview tabel */
.im-preview-wrap{overflow-x:auto;max-height:250px;overflow-y:auto}
.im-preview-table{width:100%;border-collapse:collapse;font-size:12px}
.im-preview-table th{padding:6px 10px;background:#f8f9fa;font-weight:600;white-space:nowrap;border-bottom:2px solid #dee2e6;position:sticky;top:0}
.im-preview-table td{padding:6px 10px;border-bottom:1px solid #f8f9fa;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* Validatie */
.im-errors{max-height:200px;overflow-y:auto}
.im-error-row{display:flex;gap:8px;padding:6px 10px;background:#fef2f2;border-left:3px solid #E24B4A;border-radius:0 4px 4px 0;margin-bottom:4px;font-size:12px}
.im-error-num{font-weight:700;color:#9a1e1e;flex-shrink:0}
.im-error-msg{color:#7f1d1d}
.im-valid{background:#f0faf6;border:1px solid #5DCAA5;border-radius:8px;padding:.875rem 1rem;display:flex;align-items:center;gap:10px;font-size:13px;color:#0F6E56;margin-bottom:1rem}

/* Resultaat */
.im-result-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:1rem}
.im-result-stat{background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:1rem;text-align:center}
.im-result-val{font-size:28px;font-weight:700;color:#212529}
.im-result-label{font-size:12px;color:#6c757d;margin-top:4px}

/* Knoppen */
.im-btn{padding:9px 20px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:7px;transition:background .15s;text-decoration:none}
.im-btn-primary{background:#1D9E75;color:#fff}
.im-btn-primary:hover{background:#0F6E56;color:#fff}
.im-btn-primary:disabled{background:#adb5bd;cursor:not-allowed}
.im-btn-sec{background:#f8f9fa;color:#212529;border:1px solid #dee2e6}
.im-btn-sec:hover{background:#e9ecef;color:#212529}
.im-btn-danger{background:#E24B4A;color:#fff}
.im-btn-danger:hover{background:#b91c1b;color:#fff}

.im-actions{display:flex;gap:8px;align-items:center;margin-top:1.25rem}

/* Loading */
.im-loading{display:none;align-items:center;gap:10px;font-size:13px;color:#6c757d}
.im-loading.active{display:flex}
.im-spinner{width:20px;height:20px;border:2px solid #dee2e6;border-top-color:#1D9E75;border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

.im-info{background:#f0f7ff;border:1px solid #c3ddf9;border-radius:8px;padding:.875rem 1rem;font-size:13px;color:#1a5fa6;margin-bottom:1rem;display:flex;gap:8px}
</style>

<div class="im">

    <?php /* Stap indicator */ ?>
    <div class="im-steps">
        <div class="im-step">
            <div class="im-step-num active" id="step-num-1">1</div>
            <div class="im-step-label active" id="step-label-1">Bestand uploaden</div>
        </div>
        <div class="im-step-line" id="step-line-1"></div>
        <div class="im-step">
            <div class="im-step-num pending" id="step-num-2">2</div>
            <div class="im-step-label" id="step-label-2">Koppeling instellen</div>
        </div>
        <div class="im-step-line" id="step-line-2"></div>
        <div class="im-step">
            <div class="im-step-num pending" id="step-num-3">3</div>
            <div class="im-step-label" id="step-label-3">Valideren</div>
        </div>
        <div class="im-step-line" id="step-line-3"></div>
        <div class="im-step">
            <div class="im-step-num pending" id="step-num-4">4</div>
            <div class="im-step-label" id="step-label-4">Importeren</div>
        </div>
    </div>

    <?php /* STAP 1: Upload */ ?>
    <div id="im-step-1" class="im-panel active">
        <div class="im-card">
            <div class="im-card-head">
                <div class="im-card-title">Wat wil je importeren?</div>
            </div>
            <div class="im-card-body">
                <div class="im-type-grid">
                    <button class="im-type-btn active" onclick="setType('events', this)">
                        <div class="im-type-btn-icon">📅</div>
                        <div class="im-type-btn-label">Evenementen</div>
                    </button>
                    <button class="im-type-btn" onclick="setType('venues', this)">
                        <div class="im-type-btn-icon">📍</div>
                        <div class="im-type-btn-label">Locaties</div>
                    </button>
                    <button class="im-type-btn" onclick="setType('categories', this)">
                        <div class="im-type-btn-icon">📁</div>
                        <div class="im-type-btn-label">Categorieën</div>
                    </button>
                </div>
            </div>
        </div>

        <div class="im-card">
            <div class="im-card-head">
                <div class="im-card-title">Bestand selecteren</div>
                <div class="im-card-desc">CSV (komma/puntkomma gescheiden) of JSON (Plan Je Agenda backup)</div>
            </div>
            <div class="im-card-body">
                <div class="im-dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()">
                    <div class="im-dropzone-icon" id="dz-icon">📂</div>
                    <div class="im-dropzone-title" id="dz-title">Klik om een bestand te selecteren</div>
                    <div class="im-dropzone-sub" id="dz-sub">of sleep een bestand hierheen</div>
                    <div class="im-dropzone-formats">Ondersteunde formaten: CSV, JSON (max 10 MB)</div>
                </div>
                <input type="file" id="fileInput" accept=".csv,.json,.txt" style="display:none">

                <div id="csv-options" style="margin-top:1rem">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="im-field">
                            <label>Scheidingsteken</label>
                            <select id="csv_separator" class="im-select">
                                <option value=";">Puntkomma (;)</option>
                                <option value=",">Komma (,)</option>
                                <option value="&#9;">Tab</option>
                            </select>
                        </div>
                        <div class="im-field">
                            <label>Tekstveld markering</label>
                            <select id="csv_delimiter" class="im-select">
                                <option value='"'>Aanhalingstekens (")</option>
                                <option value="'">Apostrof (')</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="im-actions">
                    <button class="im-btn im-btn-primary" id="btn-step1" onclick="uploadFile()" disabled>
                        <i class="icon-arrow-right" aria-hidden="true"></i> Volgende stap
                    </button>
                    <div class="im-loading" id="loading-1">
                        <div class="im-spinner"></div> Bestand wordt verwerkt...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php /* STAP 2: Kolomkoppeling */ ?>
    <div id="im-step-2" class="im-panel">
        <div class="im-card">
            <div class="im-card-head">
                <div class="im-card-title">Preview — eerste rijen</div>
                <div class="im-card-desc" id="preview-count"></div>
            </div>
            <div class="im-card-body" style="padding:0">
                <div class="im-preview-wrap">
                    <table class="im-preview-table" id="preview-table">
                        <thead><tr id="preview-headers"></tr></thead>
                        <tbody id="preview-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="im-card" id="mapping-card">
            <div class="im-card-head">
                <div class="im-card-title">Kolomkoppeling</div>
                <div class="im-card-desc">Koppel de CSV kolommen aan de juiste velden</div>
            </div>
            <div class="im-card-body" style="padding:0">
                <table class="im-mapping-table">
                    <thead>
                        <tr>
                            <th style="width:40%">CSV kolom</th>
                            <th>Koppelen aan veld</th>
                            <th style="width:30%">Voorbeeld waarde</th>
                        </tr>
                    </thead>
                    <tbody id="mapping-body"></tbody>
                </table>
            </div>
        </div>

        <div class="im-card">
            <div class="im-card-body">
                <div class="im-field">
                    <label>Bij duplicaten</label>
                    <select id="overwrite" class="im-select" style="max-width:300px">
                        <option value="0">Overslaan (bestaande records bewaren)</option>
                        <option value="1">Overschrijven (bestaande records bijwerken)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="im-actions">
            <button class="im-btn im-btn-sec" onclick="goToStep(1)">
                <i class="icon-arrow-left" aria-hidden="true"></i> Terug
            </button>
            <button class="im-btn im-btn-primary" onclick="validateData()">
                <i class="icon-check" aria-hidden="true"></i> Valideren
            </button>
            <div class="im-loading" id="loading-2">
                <div class="im-spinner"></div> Valideren...
            </div>
        </div>
    </div>

    <?php /* STAP 2b: JSON bevestiging */ ?>
    <div id="im-step-2b" class="im-panel">
        <div class="im-card">
            <div class="im-card-head">
                <div class="im-card-title">JSON backup inhoud</div>
                <div class="im-card-desc" id="json-export-info"></div>
            </div>
            <div class="im-card-body">
                <div id="json-summary"></div>
            </div>
        </div>

        <div class="im-card">
            <div class="im-card-body">
                <div class="im-field">
                    <label>Bij duplicaten</label>
                    <select id="overwrite-json" class="im-select" style="max-width:300px">
                        <option value="0">Overslaan (bestaande records bewaren)</option>
                        <option value="1">Overschrijven (bestaande records bijwerken)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="im-actions">
            <button class="im-btn im-btn-sec" onclick="goToStep(1)">
                <i class="icon-arrow-left" aria-hidden="true"></i> Terug
            </button>
            <button class="im-btn im-btn-danger" onclick="doJsonImport()">
                <i class="icon-upload" aria-hidden="true"></i> Import starten
            </button>
            <div class="im-loading" id="loading-2b">
                <div class="im-spinner"></div> Importeren...
            </div>
        </div>
    </div>

    <?php /* STAP 3: Validatie resultaat */ ?>
    <div id="im-step-3" class="im-panel">
        <div id="val-ok" style="display:none">
            <div class="im-valid">
                <i class="icon-ok-circle" aria-hidden="true" style="font-size:18px"></i>
                <span id="val-ok-msg"></span>
            </div>
        </div>

        <div id="val-errors" style="display:none">
            <div class="im-card">
                <div class="im-card-head">
                    <div class="im-card-title" id="val-error-title"></div>
                    <div class="im-card-desc">Controleer de data in het CSV bestand en upload opnieuw</div>
                </div>
                <div class="im-card-body">
                    <div class="im-errors" id="val-error-list"></div>
                </div>
            </div>
        </div>

        <div class="im-actions">
            <button class="im-btn im-btn-sec" onclick="goToStep(2)">
                <i class="icon-arrow-left" aria-hidden="true"></i> Terug
            </button>
            <button class="im-btn im-btn-danger" id="btn-import" onclick="doCsvImport()">
                <i class="icon-upload" aria-hidden="true"></i> Import starten
            </button>
            <div class="im-loading" id="loading-3">
                <div class="im-spinner"></div> Importeren...
            </div>
        </div>
    </div>

    <?php /* STAP 4: Resultaat */ ?>
    <div id="im-step-4" class="im-panel">
        <div class="im-card">
            <div class="im-card-head">
                <div class="im-card-title">Import voltooid</div>
            </div>
            <div class="im-card-body">
                <div class="im-result-grid">
                    <div class="im-result-stat">
                        <div class="im-result-val" id="res-inserted" style="color:#1D9E75">0</div>
                        <div class="im-result-label">Nieuw toegevoegd</div>
                    </div>
                    <div class="im-result-stat">
                        <div class="im-result-val" id="res-updated" style="color:#378ADD">0</div>
                        <div class="im-result-label">Bijgewerkt</div>
                    </div>
                    <div class="im-result-stat">
                        <div class="im-result-val" id="res-skipped" style="color:#6c757d">0</div>
                        <div class="im-result-label">Overgeslagen</div>
                    </div>
                </div>
                <div id="res-errors" style="display:none">
                    <div class="im-card" style="margin-top:1rem">
                        <div class="im-card-head">
                            <div class="im-card-title" style="color:#E24B4A">Fouten tijdens import</div>
                        </div>
                        <div class="im-card-body">
                            <div class="im-errors" id="res-error-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="im-actions">
            <a href="index.php?option=com_planjeagenda" class="im-btn im-btn-sec">
                <i class="icon-home" aria-hidden="true"></i> Terug naar dashboard
            </a>
            <button class="im-btn im-btn-primary" onclick="location.reload()">
                <i class="icon-upload" aria-hidden="true"></i> Nieuwe import
            </button>
        </div>
    </div>

</div>

<script>
// State
let state = {
    type: 'events',
    tmpFile: '',
    fileType: 'csv',
    headers: [],
    separator: ';',
    delimiter: '"',
    validCount: 0,
};

// Type selectie
function setType(type, btn) {
    state.type = type;
    document.querySelectorAll('.im-type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('csv-options').style.display = type === 'json' ? 'none' : 'block';
}

// Bestand drag & drop
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');

dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) selectFile(e.dataTransfer.files[0]);
});
fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) selectFile(fileInput.files[0]);
});

function selectFile(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['csv','json','txt'].includes(ext)) {
        alert('Alleen CSV of JSON bestanden zijn toegestaan.');
        return;
    }
    dropzone.classList.add('im-file-selected');
    document.getElementById('dz-icon').textContent = ext === 'json' ? '🗄️' : '📊';
    document.getElementById('dz-title').textContent = file.name;
    document.getElementById('dz-sub').textContent = (file.size / 1024).toFixed(1) + ' KB';
    document.getElementById('btn-step1').disabled = false;
    fileInput._selectedFile = file;

    // Verberg CSV opties bij JSON
    document.getElementById('csv-options').style.display = ext === 'json' ? 'none' : 'block';
}

// Upload bestand
async function uploadFile() {
    const file = fileInput._selectedFile || fileInput.files[0];
    if (!file) return;

    setLoading('loading-1', true);
    document.getElementById('btn-step1').disabled = true;

    const formData = new FormData();
    formData.append('import_file', file);
    formData.append('import_type', state.type);
    formData.append('csv_separator', document.getElementById('csv_separator').value);
    formData.append('csv_delimiter', document.getElementById('csv_delimiter').value);
    formData.append('task', 'import.upload');
    formData.append('<?php echo $token; ?>', '1');

    try {
        const resp = await fetch('index.php?option=com_planjeagenda', {
            method: 'POST',
            body: formData,
        });
        const data = await resp.json();

        if (data.error) {
            alert('Fout: ' + data.error);
            setLoading('loading-1', false);
            document.getElementById('btn-step1').disabled = false;
            return;
        }

        state.tmpFile   = data.tmp_file;
        state.fileType  = data.type;
        state.separator = document.getElementById('csv_separator').value;
        state.delimiter = document.getElementById('csv_delimiter').value;

        if (data.type === 'json') {
            showJsonConfirm(data);
        } else {
            showMapping(data);
        }

    } catch (e) {
        alert('Verbindingsfout: ' + e.message);
    } finally {
        setLoading('loading-1', false);
        document.getElementById('btn-step1').disabled = false;
    }
}

// Toon JSON bevestiging
function showJsonConfirm(data) {
    const info = data.export_info || {};
    document.getElementById('json-export-info').textContent =
        'Gemaakt op: ' + (info.created_at || 'onbekend') + ' · ' + (info.generator || '');

    const sum = data.summary || {};
    document.getElementById('json-summary').innerHTML = `
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px">
            <div class="im-result-stat"><div class="im-result-val" style="color:#1D9E75">${sum.evenementen||0}</div><div class="im-result-label">Evenementen</div></div>
            <div class="im-result-stat"><div class="im-result-val" style="color:#378ADD">${sum.locaties||0}</div><div class="im-result-label">Locaties</div></div>
            <div class="im-result-stat"><div class="im-result-val" style="color:#D85A30">${sum['categorieën']||0}</div><div class="im-result-label">Categorieën</div></div>
            <div class="im-result-stat"><div class="im-result-val" style="color:#6c757d">${sum.relaties||0}</div><div class="im-result-label">Relaties</div></div>
        </div>`;

    goToStepPanel('2b');
    updateStepIndicator(2);
}

// Toon kolomkoppeling
function showMapping(data) {
    state.headers = data.headers || [];

    // Preview tabel
    const headRow = document.getElementById('preview-headers');
    headRow.innerHTML = '';
    state.headers.forEach(h => {
        const th = document.createElement('th');
        th.textContent = h;
        headRow.appendChild(th);
    });

    const body = document.getElementById('preview-body');
    body.innerHTML = '';
    (data.preview || []).forEach(row => {
        const tr = document.createElement('tr');
        state.headers.forEach(h => {
            const td = document.createElement('td');
            td.textContent = row[h] || '';
            td.title = row[h] || '';
            tr.appendChild(td);
        });
        body.appendChild(tr);
    });

    document.getElementById('preview-count').textContent =
        'Totaal ' + (data.total || 0) + ' rijen — preview toont eerste 5';

    // Mapping tabel
    const mapBody = document.getElementById('mapping-body');
    mapBody.innerHTML = '';
    const fields = data.available_fields || {};

    state.headers.forEach((header, i) => {
        const firstPreview = (data.preview[0] || {})[header] || '';

        // Auto-match op basis van naam
        let autoMatch = '';
        const headerLower = header.toLowerCase().replace(/[^a-z0-9]/g, '');
        Object.keys(fields).forEach(field => {
            const fieldLower = field.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (headerLower === fieldLower || headerLower.includes(fieldLower) || fieldLower.includes(headerLower)) {
                autoMatch = field;
            }
        });

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight:500">${header}</td>
            <td>
                <select class="im-mapping-select" name="mapping[${header}]">
                    <option value="">— niet importeren —</option>
                    ${Object.entries(fields).map(([f,l]) =>
                        `<option value="${f}" ${autoMatch===f?'selected':''}>${l}</option>`
                    ).join('')}
                </select>
            </td>
            <td style="color:#6c757d;font-size:12px">${firstPreview}</td>
        `;
        mapBody.appendChild(tr);
    });

    goToStepPanel('2');
    updateStepIndicator(2);
}

// Valideer data
async function validateData() {
    const mapping = getMapping();

    setLoading('loading-2', true);

    const body = new URLSearchParams({
        'option': 'com_planjeagenda',
        'task': 'import.validate',
        'tmp_file': state.tmpFile,
        'import_type': state.type,
        'csv_separator': state.separator,
        'csv_delimiter': state.delimiter,
        'overwrite': document.getElementById('overwrite').value,
        '<?php echo $token; ?>': '1',
    });

    Object.entries(mapping).forEach(([k,v]) => {
        body.append('mapping[' + k + ']', v);
    });

    try {
        const resp = await fetch('index.php?option=com_planjeagenda', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body.toString(),
        });
        const data = await resp.json();

        state.validCount = data.valid || 0;

        document.getElementById('val-ok').style.display = 'none';
        document.getElementById('val-errors').style.display = 'none';

        if (data.valid > 0) {
            document.getElementById('val-ok').style.display = 'block';
            document.getElementById('val-ok-msg').textContent =
                data.valid + ' rij' + (data.valid !== 1 ? 'en' : '') + ' zijn geldig en klaar voor import.';
        }

        if (data.errors && data.errors.length > 0) {
            document.getElementById('val-errors').style.display = 'block';
            document.getElementById('val-error-title').textContent =
                data.errors.length + ' fout' + (data.errors.length !== 1 ? 'en' : '') + ' gevonden';

            const list = document.getElementById('val-error-list');
            list.innerHTML = data.errors.map(e =>
                `<div class="im-error-row">
                    <span class="im-error-num">${e.row > 0 ? 'Rij ' + e.row : 'Algemeen'}</span>
                    <span class="im-error-msg">${e.message}</span>
                 </div>`
            ).join('');
        }

        document.getElementById('btn-import').disabled = data.valid === 0;
        goToStepPanel('3');
        updateStepIndicator(3);

    } catch (e) {
        alert('Verbindingsfout: ' + e.message);
    } finally {
        setLoading('loading-2', false);
    }
}

// CSV importeren
async function doCsvImport() {
    setLoading('loading-3', true);
    document.getElementById('btn-import').disabled = true;

    const mapping = getMapping();
    const body = new URLSearchParams({
        'option': 'com_planjeagenda',
        'task': 'import.doImport',
        'tmp_file': state.tmpFile,
        'import_type': state.type,
        'file_type': 'csv',
        'csv_separator': state.separator,
        'csv_delimiter': state.delimiter,
        'overwrite': document.getElementById('overwrite').value,
        '<?php echo $token; ?>': '1',
    });
    Object.entries(mapping).forEach(([k,v]) => body.append('mapping[' + k + ']', v));

    await doImportRequest(body.toString(), 'application/x-www-form-urlencoded');
    setLoading('loading-3', false);
}

// JSON importeren
async function doJsonImport() {
    setLoading('loading-2b', true);

    const body = new URLSearchParams({
        'option': 'com_planjeagenda',
        'task': 'import.doImport',
        'tmp_file': state.tmpFile,
        'file_type': 'json',
        'overwrite': document.getElementById('overwrite-json').value,
        '<?php echo $token; ?>': '1',
    });

    await doImportRequest(body.toString(), 'application/x-www-form-urlencoded');
    setLoading('loading-2b', false);
}

async function doImportRequest(body, contentType) {
    try {
        const resp = await fetch('index.php?option=com_planjeagenda', {
            method: 'POST',
            headers: {'Content-Type': contentType},
            body: body,
        });
        const data = await resp.json();

        document.getElementById('res-inserted').textContent = data.inserted || 0;
        document.getElementById('res-updated').textContent  = data.updated  || 0;
        document.getElementById('res-skipped').textContent  = data.skipped  || 0;

        if (data.errors && data.errors.length > 0) {
            document.getElementById('res-errors').style.display = 'block';
            document.getElementById('res-error-list').innerHTML = data.errors.map(e =>
                `<div class="im-error-row"><span class="im-error-msg">${e}</span></div>`
            ).join('');
        }

        goToStepPanel('4');
        updateStepIndicator(4);

    } catch (e) {
        alert('Verbindingsfout: ' + e.message);
    }
}

// Hulpfuncties
function getMapping() {
    const mapping = {};
    document.querySelectorAll('#mapping-body select').forEach(sel => {
        const name = sel.name.match(/\[(.+)\]/)[1];
        if (sel.value) mapping[name] = sel.value;
    });
    return mapping;
}

function goToStep(n) {
    goToStepPanel(n.toString());
    updateStepIndicator(n);
}

function goToStepPanel(id) {
    document.querySelectorAll('.im-panel').forEach(p => p.classList.remove('active'));
    const panel = document.getElementById('im-step-' + id);
    if (panel) panel.classList.add('active');
}

function updateStepIndicator(activeStep) {
    [1,2,3,4].forEach(n => {
        const num   = document.getElementById('step-num-' + n);
        const label = document.getElementById('step-label-' + n);
        const line  = document.getElementById('step-line-' + n);

        num.className = 'im-step-num ' + (n < activeStep ? 'done' : n === activeStep ? 'active' : 'pending');
        label.className = 'im-step-label' + (n === activeStep ? ' active' : '');
        if (line) line.className = 'im-step-line' + (n < activeStep ? ' done' : '');
    });
}

function setLoading(id, show) {
    document.getElementById(id).classList.toggle('active', show);
}
</script>
