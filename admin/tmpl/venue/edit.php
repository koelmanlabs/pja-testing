<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

$item        = $this->item;
$isNew       = ($item->id == 0);
$attachments = $this->attachments ?? [];

// Config ophalen
$params      = ComponentHelper::getParams('com_planjeagenda');
$maxWidth    = $params->get('image_max_width', 1200);
$maxHeight   = $params->get('image_max_height', 1200);
$quality     = $params->get('image_quality', 80);
$allowedMime = $params->get('allowed_extensions', 'image/png, image/jpeg, image/webp, application/pdf, video/mp4, text/plain');

$doc = Factory::getDocument();
// Assets
$doc->addStyleSheet('https://unpkg.com/filepond/dist/filepond.css');
$doc->addStyleSheet('https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css');

$doc->addScript('https://cdn.jsdelivr.net/npm/sweetalert2@11');
$doc->addScript('https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js');
$doc->addScript('https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js');
$doc->addScript('https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js');
$doc->addScript('https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js');
$doc->addScript('https://unpkg.com/filepond/dist/filepond.js');
?>

<style>
    /* Algemene Grid */
    .pja-attachment-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1.5rem; }
    .pja-attachment-card { border: 1px solid #dee2e6; border-radius: 8px; background: #fff; padding: 12px; text-align: center; }
    .pja-attachment-thumb { 
        height: 110px; display: flex; align-items: center; justify-content: center; 
        margin-bottom: 10px; background: #f8f9fa; border-radius: 5px; overflow: hidden; 
        font-size: 2.5rem; color: #6c757d; cursor: zoom-in;
    }
    .pja-attachment-thumb img { max-width: 100%; max-height: 100%; object-fit: cover; }
    .pja-attachment-name { font-size: 11px; font-weight: 600; display: block; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; margin-bottom: 8px; }
    
    /* FilePond Customization - Weg met de zwarte kaders */
    .filepond--panel-root { background-color: #fdfdfd; border: 2px dashed #007bff; }
    .filepond--item { width: calc(50% - 0.5em); }
    .filepond--image-preview-wrapper { border-radius: 8px; }
    .filepond--image-preview { background: #222; } /* Subtiele achtergrond voor transparante PNGs */
    
    /* Instructie Alert Styling */
    .pja-upload-instruction {
        background-color: #fff3cd;
        border-left: 5px solid #ffc107;
        color: #856404;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
</style>

<form action="<?php echo Uri::base(); ?>index.php?option=com_planjeagenda&view=venue&layout=edit&id=<?php echo (int) $item->id; ?>" 
      method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

    <div class="row">
        <div class="col-lg-9">
            <?php echo HTMLHelper::_('uitab.startTabSet', 'venueTabs', ['active' => 'details']); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'venueTabs', 'details', 'Locatie Details'); ?>
                <div class="card border-top-0 rounded-0 p-4">
                    <?php echo $this->form->renderFieldset('details'); ?>
                </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'venueTabs', 'attachments', 'Bijlagen (' . count($attachments) . ')'); ?>
                <div class="card border-top-0 rounded-0 p-4">
                    <?php if ($isNew) : ?>
                        <div class="alert alert-info">Sla de locatie eerst op om bijlagen toe te voegen.</div>
                    <?php else : ?>
                        
                        <!-- Instructie tekst toegevoegd -->
                        <div class="pja-upload-instruction shadow-sm">
                            <i class="icon-info-circle me-2"></i>
                            <strong>Let op:</strong> Na het selecteren van bestanden dien je op het 
                            <span class="badge bg-primary"><i class="icon-upload"></i> upload-icoontje</span> 
                            bij de foto in de wachtrij te klikken om de verwerking te starten.
                        </div>

                        <div class="mb-4">
                            <input type="file" class="filepond" name="file" multiple>
                        </div>

                        <div class="pja-attachment-grid">
                            <?php foreach ($attachments as $attachment) : ?>
                                <?php 
                                    $type = strtolower($attachment->filetype);
                                    $filePath = Uri::root() . $attachment->path;
                                ?>
                                <div class="pja-attachment-card shadow-sm" id="attachment-<?php echo $attachment->id; ?>">
                                    <div class="pja-attachment-thumb border" onclick="previewAttachment('<?php echo $filePath; ?>', '<?php echo $type; ?>', '<?php echo $attachment->filename; ?>')">
                                        <?php if (strpos($type, 'image') !== false) : ?>
                                            <img src="<?php echo $filePath; ?>">
                                        <?php elseif (strpos($type, 'video') !== false || strpos($type, 'mp4') !== false) : ?>
                                            <i class="icon-video text-primary"></i>
                                        <?php elseif (strpos($type, 'pdf') !== false) : ?>
                                            <i class="icon-file-pdf text-danger"></i>
                                        <?php else : ?>
                                            <i class="icon-file"></i>
                                        <?php endif; ?>
                                    </div>
                                    <span class="pja-attachment-name" title="<?php echo $attachment->filename; ?>"><?php echo $attachment->filename; ?></span>
                                    
                                    <div class="btn-group w-100">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="previewAttachment('<?php echo $filePath; ?>', '<?php echo $type; ?>', '<?php echo $attachment->filename; ?>')">
                                            <i class="icon-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="removeExistingAttachment(<?php echo $attachment->id; ?>)">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
        
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php echo $this->form->renderFieldset('sidebar'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    FilePond.registerPlugin(
        FilePondPluginFileValidateType, 
        FilePondPluginImagePreview,
        FilePondPluginImageResize,
        FilePondPluginImageTransform
    );

    const pond = FilePond.create(document.querySelector('input.filepond'), {
        labelIdle: 'Sleep bestanden hierheen of <span class="filepond--label-action">Blader</span>',
        allowMultiple: true,
        instantUpload: false, 
        credits: false,
        acceptedFileTypes: "<?php echo $allowedMime; ?>".split(',').map(s => s.trim()),
        
        // Image settings
        allowImageResize: true,
        imageResizeTargetWidth: <?php echo (int) $maxWidth; ?>,
        imageResizeTargetHeight: <?php echo (int) $maxHeight; ?>,
        imageTransformOutputQuality: <?php echo (int) $quality; ?>,

        server: {
            process: {
                url: 'index.php?option=com_planjeagenda&task=venue.upload&format=json',
                method: 'POST',
                headers: { 'X-CSRF-Token': '<?php echo Session::getFormToken(); ?>' },
                ondata: (formData) => {
                    formData.append('id', '<?php echo (int) $item->id; ?>');
                    formData.append('<?php echo Session::getFormToken(); ?>', '1');
                    return formData;
                }
            }
        },
        // Blokkeer form submission
        oninit: () => {
            const pondRoot = document.querySelector('.filepond--root');
            pondRoot.addEventListener('click', e => {
                if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                    const btn = e.target.closest('button');
                    if (!btn.classList.contains('filepond--action-revert-item-processing')) {
                         // We laten FilePond zijn eigen ding doen maar voorkomen de form-submit
                    }
                }
            });
        },
        // Optioneel: Herlaad als alles klaar is
        onprocessfiles: () => {
            Swal.fire({
                title: 'Upload voltooid',
                text: 'De pagina wordt ververst om de bijlagen te tonen.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        }
    });

    // Joomla Save Beveiliging
    const saveButtons = document.querySelectorAll('.button-save, .button-apply, [data-task$=".save"], [data-task$=".apply"]');
    saveButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const files = pond.getFiles();
            const hasQueued = files.some(f => f.status === 2); // Status 2 = Idle (in wachtrij)
            
            if (hasQueued) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    title: 'Bestanden in wachtrij',
                    text: 'Er staan nog bestanden klaar die niet zijn geüpload. Klik op het upload-icoontje bij de foto\'s of verwijder ze uit de wachtrij.'
                });
            }
        });
    });
});

function previewAttachment(url, type, filename) {
    let content = '';
    if (type.includes('image')) {
        content = `<img src="${url}" style="max-width:100%; border-radius:5px;">`;
    } else if (type.includes('video') || type.includes('mp4')) {
        content = `<video controls style="width:100%;"><source src="${url}" type="video/mp4"></video>`;
    } else if (type.includes('pdf')) {
        content = `<iframe src="${url}" style="width:100%; height:500px;" frameborder="0"></iframe>`;
    } else if (type.includes('text')) {
        fetch(url).then(r => r.text()).then(t => {
            Swal.fire({ title: filename, html: `<pre style="text-align:left; background:#eee; padding:10px;">${t}</pre>`, width: '800px'});
        });
        return;
    } else {
        content = `<p>Geen preview beschikbaar.</p><a href="${url}" class="btn btn-primary" download>Download</a>`;
    }

    Swal.fire({
        title: filename,
        html: content,
        width: type.includes('pdf') ? '90%' : '600px',
        showCloseButton: true,
        showConfirmButton: false
    });
}

function removeExistingAttachment(id) {
    Swal.fire({
        title: 'Verwijderen?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ja, wis bestand'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('<?php echo Session::getFormToken(); ?>', '1');
            fetch('index.php?option=com_planjeagenda&task=venue.ajaxRemoveAttachment&format=json', {
                method: 'POST',
                body: formData
            }).then(() => {
                document.getElementById('attachment-' + id).remove();
            });
        }
    });
}





document.addEventListener('DOMContentLoaded', function() {
	var muniSelect    = document.getElementById('jform_municipality_id');
	var villageSelect = document.getElementById('jform_village_id');

	if (!muniSelect || !villageSelect) return;

	muniSelect.addEventListener('change', function() {
		var muniId = this.value;

		// Als er geen gemeente is gekozen, maak het dorpenveld leeg
		if (!muniId) {
			villageSelect.innerHTML = '<option value=""><?php echo Text::_('COM_PLANJEAGENDA_SELECT_VILLAGE'); ?></option>';
			return;
		}

		// Haal de token op uit het formulier
		var token = document.querySelector('input[name*="[token]"]') ? document.querySelector('input[name*="[token]"]').name : '';
		if (!token) {
			// Fallback als de token direct in de form staat
			token = document.querySelector('input[name="token"]') ? 'token' : '';
		}

		// Bouw de AJAX URL op naar de zojuist gemaakte controller
		var url = 'index.php?option=com_planjeagenda&task=villages.getVillagesByMunicipality&municipality_id=' + muniId + '&' + token + '=1';

		fetch(url)
			.then(response => response.json())
			.then(data => {
				// Wis huidige opties
				villageSelect.innerHTML = '<option value=""><?php echo Text::_('COM_PLANJEAGENDA_SELECT_VILLAGE'); ?></option>';

				// Voeg de nieuwe dorpen toe
				data.forEach(function(village) {
					var option = document.createElement('option');
					option.value = village.id;
					option.text  = village.title;
					villageSelect.appendChild(option);
				});
			})
			.catch(error => console.error('Fout bij ophalen dorpen:', error));
	});
});






</script>