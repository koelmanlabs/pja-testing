<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * Frontend attachment tab — hybrid FilePond (local/form-submit mode) + card grid.
 * Field names are identical to the old template so the save controller needs no changes.
 * FilePond runs in storeAsFile mode: files are submitted with the normal form POST.
 * Existing attachments use the existing AJAX remove endpoint (ajaxattachremove).
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$attachments      = $this->item->attachments ?? [];
$attachEnabled    = ($this->jemsettings->attachmentenabled != 0);
$token            = Session::getFormToken();
$baseUrl          = \Uri::root();
$allowedTypes     = $this->jemsettings->attachments_types ?? 'image/jpeg,image/png,image/webp,application/pdf';
$maxSizeKb        = (int)($this->jemsettings->attachments_maxsize ?? 2048);
$maxSizeBytes     = $maxSizeKb * 1024;

// Load FilePond from CDN
$doc = \Factory::getApplication()->getDocument();
$doc->addStyleSheet('https://unpkg.com/filepond@4/dist/filepond.css');
$doc->addStyleSheet('https://unpkg.com/filepond-plugin-image-preview@4/dist/filepond-plugin-image-preview.css');
$doc->addScript('https://unpkg.com/filepond-plugin-file-validate-type@1/dist/filepond-plugin-file-validate-type.js');
$doc->addScript('https://unpkg.com/filepond-plugin-file-validate-size@2/dist/filepond-plugin-file-validate-size.js');
$doc->addScript('https://unpkg.com/filepond-plugin-image-preview@4/dist/filepond-plugin-image-preview.js');
$doc->addScript('https://unpkg.com/filepond@4/dist/filepond.js');
?>

<style>
/* ── Attachment section ─────────────────────────────────────────── */
.pja-att-section {
    padding: 1.25rem 0;
}
/* FilePond drop zone */
.pja-att-section .filepond--panel-root {
    background: #f8f9fa;
    border: 2px dashed #2e7d32;
    border-radius: 8px;
}
.pja-att-section .filepond--drop-label {
    color: #495057;
    font-size: .9rem;
}
.pja-att-section .filepond--label-action {
    color: #2e7d32;
    font-weight: 700;
    text-decoration-color: #2e7d32;
}

/* Existing attachments grid */
.pja-att-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1.25rem;
}
.pja-att-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    display: flex;
    flex-direction: column;
}
.pja-att-thumb {
    height: 90px;
    background: #f1f3f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #6c757d;
    overflow: hidden;
    flex-shrink: 0;
}
.pja-att-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.pja-att-body {
    padding: .75rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: .45rem;
}
.pja-att-body label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin: 0;
    display: block;
}
.pja-att-body input[type="text"],
.pja-att-body select {
    width: 100%;
    padding: .3rem .5rem;
    font-size: .82rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background: #fff;
    color: #212529;
}
.pja-att-body input[type="text"]:focus,
.pja-att-body select:focus {
    outline: none;
    border-color: #2e7d32;
    box-shadow: 0 0 0 2px rgba(46,125,50,.15);
}
.pja-att-filename {
    font-size: .78rem;
    color: #495057;
    word-break: break-all;
    background: #f8f9fa;
    padding: .25rem .4rem;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}
.pja-att-footer {
    padding: .5rem .75rem;
    border-top: 1px solid #f1f3f5;
    display: flex;
    justify-content: flex-end;
}
.pja-att-remove {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .3rem .7rem;
    font-size: .78rem;
    font-weight: 600;
    color: #dc3545;
    background: transparent;
    border: 1px solid #dc3545;
    border-radius: 4px;
    cursor: pointer;
    transition: background .15s, color .15s;
    text-decoration: none;
}
.pja-att-remove:hover {
    background: #dc3545;
    color: #fff;
}
.pja-att-empty {
    color: #6c757d;
    font-size: .9rem;
    font-style: italic;
    padding: .5rem 0;
}
.pja-att-section h6 {
    font-size: .85rem;
    font-weight: 700;
    color: #495057;
    margin: 1.25rem 0 .6rem;
    text-transform: uppercase;
    letter-spacing: .04em;
}
/* Info notice */
.pja-att-notice {
    background: #e8f5e9;
    border-left: 4px solid #2e7d32;
    padding: .6rem 1rem;
    border-radius: 0 5px 5px 0;
    font-size: .83rem;
    color: #2e7d32;
    margin-bottom: 1rem;
}
</style>

<div class="pja-att-section">

<?php if ($attachEnabled): ?>
    <div class="pja-att-notice">
        <i class="fa fa-info-circle"></i>
        <?php echo \Text::_('COM_PLANJEAGENDA_ATTACHMENT_UPLOAD_HINT'); ?>
    </div>

    <!-- FilePond drop zone — submits with form via storeAsFile mode -->
    <input type="file"
           id="pja-filepond-venue"
           name="attach[]"
           class="pja-filepond-input"
           multiple
           accept="<?php echo htmlspecialchars($allowedTypes); ?>" />
<?php endif; ?>

<?php if (!empty($attachments)): ?>
    <h6><?php echo \Text::_('COM_PLANJEAGENDA_CURRENT_ATTACHMENTS'); ?></h6>
    <div class="pja-att-grid">
    <?php foreach ($attachments as $att):
        $ext      = strtolower(pathinfo($att->file, PATHINFO_EXTENSION));
        $isImage  = in_array($ext, ['jpg','jpeg','png','gif','webp','svg']);
        $isPdf    = $ext === 'pdf';
        $isVideo  = in_array($ext, ['mp4','mov','avi','webm']);
        $icon     = $isPdf ? 'fa-file-pdf' : ($isVideo ? 'fa-file-video' : 'fa-file');
        $filePath = $baseUrl . $this->jemsettings->attachments_path . '/venue' . (int)$this->item->id . '/' . $att->file;
    ?>
        <div class="pja-att-card" id="pja-att-card-<?php echo (int)$att->id; ?>">
            <!-- Thumbnail -->
            <div class="pja-att-thumb">
                <?php if ($isImage): ?>
                    <img src="<?php echo htmlspecialchars($filePath); ?>"
                         alt="<?php echo htmlspecialchars($att->file); ?>"
                         loading="lazy" />
                <?php else: ?>
                    <i class="fa <?php echo $icon; ?>"></i>
                <?php endif; ?>
            </div>

            <!-- Editable fields -->
            <div class="pja-att-body">
                <input type="hidden" name="attached-id[]" value="<?php echo (int)$att->id; ?>" />

                <div class="pja-att-filename" title="<?php echo htmlspecialchars($att->file); ?>">
                    <?php echo htmlspecialchars($att->file); ?>
                </div>

                <div>
                    <label><?php echo \Text::_('COM_PLANJEAGENDA_ATTACHMENT_NAME'); ?></label>
                    <input type="text"
                           name="attached-name[]"
                           value="<?php echo htmlspecialchars($att->name ?? ''); ?>" />
                </div>

                <div>
                    <label><?php echo \Text::_('COM_PLANJEAGENDA_ATTACHMENT_DESCRIPTION'); ?></label>
                    <input type="text"
                           name="attached-desc[]"
                           value="<?php echo htmlspecialchars($att->description ?? ''); ?>" />
                </div>

                <div>
                    <label><?php echo \Text::_('COM_PLANJEAGENDA_ATTACHMENT_ACCESS'); ?></label>
                    <?php
                    $attribs = ['class' => 'form-select form-select-sm'];
                    if (!$attachEnabled) $attribs['disabled'] = 'disabled';
                    echo \HTMLHelper::_('select.genericlist',
                        $this->access, 'attached-access[]', $attribs, 'value', 'text', $att->access);
                    ?>
                </div>
            </div>

            <!-- Remove button -->
            <?php if ($attachEnabled): ?>
            <div class="pja-att-footer">
                <button type="button"
                        class="pja-att-remove attach-remove"
                        id="attach-remove<?php echo $att->id; ?>:<?php echo $token; ?>"
                        data-card="pja-att-card-<?php echo (int)$att->id; ?>"
                        title="<?php echo \Text::_('COM_PLANJEAGENDA_ATTACHMENT_REMOVE'); ?>">
                    <i class="fa fa-trash"></i>
                    <?php echo \Text::_('COM_PLANJEAGENDA_ATTACHMENT_REMOVE'); ?>
                </button>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
<?php elseif (!$attachEnabled): ?>
    <p class="pja-att-empty"><?php echo \Text::_('COM_PLANJEAGENDA_NO_ATTACHMENTS_FOUND'); ?></p>
<?php endif; ?>

</div><!-- .pja-att-section -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── FilePond (form-submit / local mode) ───────────────────────────
    const pondInput = document.getElementById('pja-filepond-venue');
    if (pondInput && typeof FilePond !== 'undefined') {
        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginFileValidateSize,
            FilePondPluginImagePreview
        );

        FilePond.create(pondInput, {
            // No server — files stay local and submit with the form
            server: null,
            storeAsFile: true,
            allowMultiple: true,
            maxFileSize: '<?php echo $maxSizeKb; ?>KB',
            acceptedFileTypes: <?php echo json_encode(array_map('trim', explode(',', $allowedTypes))); ?>,
            labelIdle: 'Sleep bestanden hierheen of <span class="filepond--label-action">Bladeren</span>',
            labelMaxFileSizeExceeded: 'Bestand is te groot',
            labelMaxFileSize: 'Maximale bestandsgrootte is {filesize}',
            labelFileTypeNotAllowed: 'Bestandstype niet toegestaan',
            labelFileProcessing: 'Bezig…',
            labelTapToCancel: 'tikken om te annuleren',
            labelTapToRetry: 'tikken om opnieuw te proberen',
            labelTapToUndo: 'tikken om ongedaan te maken',
            credits: false,
        });
    }

    // ── Remove existing attachment (AJAX — existing endpoint) ─────────
    document.querySelectorAll('.pja-att-remove').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const btnId  = btn.getAttribute('id'); // "attach-remove123:token"
            const pos    = btnId.indexOf(':');
            const id     = btnId.substring(13, pos > 0 ? pos : undefined);
            const token  = pos > 0 ? btnId.substr(pos + 1) : '';
            const cardId = btn.dataset.card;

            btn.disabled = true;
            btn.style.opacity = '.5';

            let url = 'index.php?option=com_planjeagenda&task=ajaxattachremove&format=raw&id=' + id;
            if (token) url += '&' + token + '=1';

            fetch(url, { method: 'POST' })
                .then(r => r.text())
                .then(function (resp) {
                    if (resp.indexOf('1') > -1) {
                        const card = document.getElementById(cardId);
                        if (card) {
                            card.style.transition = 'opacity .25s';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 260);
                        }
                    } else {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        alert('<?php echo \Text::_('COM_PLANJEAGENDA_ATTACHMENT_REMOVE_FAILED'); ?>');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
        });
    });
});
</script>
