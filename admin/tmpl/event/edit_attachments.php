<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$item        = $this->item;
$objectId    = (int) $item->id;
$attachments = $item->attachments ?? [];
$isNew       = ($objectId === 0);

// Config
$params      = ComponentHelper::getParams('com_planjeagenda');
$maxWidth    = $params->get('image_max_width', 1200);
$maxHeight   = $params->get('image_max_height', 1200);
$quality     = $params->get('image_quality', 80);
$allowedMime = $params->get('allowed_extensions', 'image/png, image/jpeg, image/webp, application/pdf, video/mp4, text/plain');

$doc = Factory::getDocument();
// SweetAlert2 (no local fallback needed — admin only, less critical)
$doc->addScript('https://cdn.jsdelivr.net/npm/sweetalert2@11');

// Use local FilePond assets if plg_system_pja_assets registered them
$wa     = $doc->getWebAssetManager();
$fpAssets = [
    'styles'  => ['pja.filepond', 'pja.filepond.preview'],
    'scripts' => ['pja.filepond.validate-type', 'pja.filepond.preview',
                  'pja.filepond.resize', 'pja.filepond.transform', 'pja.filepond'],
];
if ($wa->assetExists('script', 'pja.filepond')) {
    foreach ($fpAssets['styles']  as $a) { if ($wa->assetExists('style',  $a)) $wa->useStyle($a);  }
    foreach ($fpAssets['scripts'] as $a) { if ($wa->assetExists('script', $a)) $wa->useScript($a); }
} else {
    // CDN fallback
    $doc->addStyleSheet('https://unpkg.com/filepond/dist/filepond.min.css');
    $doc->addStyleSheet('https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css');
    $doc->addScript('https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js');
    $doc->addScript('https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js');
    $doc->addScript('https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.min.js');
    $doc->addScript('https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.min.js');
    $doc->addScript('https://unpkg.com/filepond/dist/filepond.min.js');
}
?>

<style>
    .pja-attachment-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1.5rem; }
    .pja-attachment-card { border: 1px solid #dee2e6; border-radius: 8px; background: #fff; padding: 12px; text-align: center; }
    .pja-attachment-thumb {
        height: 110px; display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px; background: #f8f9fa; border-radius: 5px; overflow: hidden;
        font-size: 2.5rem; color: #6c757d; cursor: zoom-in;
    }
    .pja-attachment-thumb img { max-width: 100%; max-height: 100%; object-fit: cover; }
    .pja-attachment-name { font-size: 11px; font-weight: 600; display: block; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; margin-bottom: 8px; }
    .filepond--panel-root { background-color: #fdfdfd; border: 2px dashed #007bff; }
    .filepond--item { width: calc(50% - 0.5em); }
    .filepond--image-preview-wrapper { border-radius: 8px; }
    .filepond--image-preview { background: #222; }
    .pja-upload-instruction {
        background-color: #fff3cd; border-left: 5px solid #ffc107;
        color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 4px;
    }
</style>

<div class="card mt-3">
    <div class="card-body">

        <?php if ($isNew) : ?>
            <div class="alert alert-info">
                <i class="icon-info-circle me-2"></i>
                <?php echo Text::_('com_planjeagenda_SAVE_FIRST_TO_UPLOAD'); ?>
            </div>
        <?php else : ?>

            <div class="pja-upload-instruction shadow-sm">
                <i class="icon-info-circle me-2"></i>
                <strong>Let op:</strong> Na het selecteren van bestanden dien je op het
                <span class="badge bg-primary"><i class="icon-upload"></i> upload-icoontje</span>
                bij de foto in de wachtrij te klikken om de verwerking te starten.
            </div>

            <div class="mb-4">
                <input type="file" class="filepond-event" name="file" multiple>
            </div>

            <hr>

            <h5><?php echo Text::_('com_planjeagenda_CURRENT_ATTACHMENTS'); ?></h5>
            <div class="pja-attachment-grid" id="pja-event-attachment-grid">
                <?php foreach ($attachments as $attachment) :
                    $type     = strtolower($attachment->filetype ?? pathinfo($attachment->filename ?? '', PATHINFO_EXTENSION));
                    $filePath = Uri::root() . ($attachment->filepath ?? $attachment->path ?? '');
                ?>
                    <div class="pja-attachment-card shadow-sm" id="attachment-event-<?php echo $attachment->id; ?>">
                        <div class="pja-attachment-thumb border"
                             onclick="pjaPreviewAttachment('<?php echo $filePath; ?>', '<?php echo $type; ?>', '<?php echo htmlspecialchars($attachment->filename, ENT_QUOTES); ?>')">
                            <?php if (strpos($type, 'image') !== false || in_array($type, ['jpg','jpeg','png','gif','webp'])) : ?>
                                <img src="<?php echo $filePath; ?>" alt="<?php echo htmlspecialchars($attachment->filename, ENT_QUOTES); ?>">
                            <?php elseif (strpos($type, 'video') !== false || $type === 'mp4') : ?>
                                <i class="icon-video text-primary"></i>
                            <?php elseif (strpos($type, 'pdf') !== false || $type === 'pdf') : ?>
                                <i class="icon-file-pdf text-danger"></i>
                            <?php else : ?>
                                <i class="icon-file"></i>
                            <?php endif; ?>
                        </div>
                        <span class="pja-attachment-name" title="<?php echo htmlspecialchars($attachment->filename, ENT_QUOTES); ?>">
                            <?php echo htmlspecialchars($attachment->filename, ENT_QUOTES); ?>
                        </span>
                        <div class="btn-group w-100">
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                    onclick="pjaPreviewAttachment('<?php echo $filePath; ?>', '<?php echo $type; ?>', '<?php echo htmlspecialchars($attachment->filename, ENT_QUOTES); ?>')">
                                <i class="icon-search"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm"
                                    onclick="pjaRemoveEventAttachment(<?php echo $attachment->id; ?>)">
                                <i class="icon-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($attachments)) : ?>
                    <p class="text-muted fst-italic col-12"><?php echo Text::_('com_planjeagenda_NO_ATTACHMENTS_FOUND'); ?></p>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php if (!$isNew) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof FilePond === 'undefined') return;

    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginImageResize,
        FilePondPluginImageTransform
    );

    const pondInput = document.querySelector('input.filepond-event');
    if (!pondInput) return;

    const pond = FilePond.create(pondInput, {
        labelIdle: 'Sleep bestanden hierheen of <span class="filepond--label-action">Blader</span>',
        allowMultiple: true,
        instantUpload: false,
        credits: false,
        acceptedFileTypes: "<?php echo addslashes($allowedMime); ?>".split(',').map(s => s.trim()),
        allowImageResize: true,
        imageResizeTargetWidth: <?php echo (int) $maxWidth; ?>,
        imageResizeTargetHeight: <?php echo (int) $maxHeight; ?>,
        imageTransformOutputQuality: <?php echo (int) $quality; ?>,
        server: {
            process: {
                url: 'index.php?option=com_planjeagenda&task=event.upload&format=json',
                method: 'POST',
                headers: { 'X-CSRF-Token': '<?php echo Session::getFormToken(); ?>' },
                ondata: (formData) => {
                    formData.append('id', '<?php echo $objectId; ?>');
                    formData.append('<?php echo Session::getFormToken(); ?>', '1');
                    return formData;
                }
            }
        },
        onprocessfiles: () => {
            Swal.fire({
                title: 'Upload voltooid',
                text: 'De pagina wordt ververst om de bijlagen te tonen.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => window.location.reload());
        }
    });

    // Blokkeer form-submit als er nog bestanden in de wachtrij staan
    const saveButtons = document.querySelectorAll('.button-save, .button-apply, [data-task$=".save"], [data-task$=".apply"]');
    saveButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const hasQueued = pond.getFiles().some(f => f.status === 2);
            if (hasQueued) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    title: 'Bestanden in wachtrij',
                    text: 'Er staan nog bestanden klaar die niet zijn geüpload. Klik op het upload-icoontje of verwijder ze uit de wachtrij.'
                });
            }
        });
    });
});

function pjaPreviewAttachment(url, type, filename) {
    let content = '';
    if (type.includes('image') || ['jpg','jpeg','png','gif','webp'].includes(type)) {
        content = `<img src="${url}" style="max-width:100%; border-radius:5px;">`;
    } else if (type.includes('video') || type === 'mp4') {
        content = `<video controls style="width:100%;"><source src="${url}" type="video/mp4"></video>`;
    } else if (type.includes('pdf') || type === 'pdf') {
        content = `<iframe src="${url}" style="width:100%; height:500px;" frameborder="0"></iframe>`;
    } else if (type.includes('text') || type === 'txt') {
        fetch(url).then(r => r.text()).then(t => {
            Swal.fire({ title: filename, html: `<pre style="text-align:left; background:#eee; padding:10px; overflow:auto;">${t}</pre>`, width: '800px' });
        });
        return;
    } else {
        content = `<p>Geen preview beschikbaar.</p><a href="${url}" class="btn btn-primary" download>Download</a>`;
    }
    Swal.fire({
        title: filename,
        html: content,
        width: (type.includes('pdf') || type === 'pdf') ? '90%' : '600px',
        showCloseButton: true,
        showConfirmButton: false
    });
}

function pjaRemoveEventAttachment(id) {
    Swal.fire({
        title: 'Verwijderen?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ja, wis bestand',
        cancelButtonText: 'Annuleren'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('<?php echo Session::getFormToken(); ?>', '1');
            fetch('index.php?option=com_planjeagenda&task=event.ajaxRemoveAttachment&format=json', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    document.getElementById('attachment-event-' + id)?.remove();
                } else {
                    Swal.fire('Fout', data.message || 'Verwijderen mislukt.', 'error');
                }
            });
        }
    });
}
</script>
<?php endif; ?>
