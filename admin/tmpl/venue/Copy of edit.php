<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

// Essentiële Joomla scripts
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tab'); // Belangrijk voor de werking van de tabs!

$attachments = $this->attachments ?? [];
?>
<style>
    /* Dropzone Box Styling */
    .dropzone { border: 2px dashed #007bff; background: #f8f9fa; border-radius: 5px; min-height: 150px; }
    .dropzone .dz-message { font-weight: 400; color: #666; margin: 2em 0; }
    
    /* Fix voor missende icons/thumbnails */
    .dropzone .dz-preview .dz-image img { width: 100%; height: auto; }
    .dropzone .dz-preview .dz-remove { color: #dc3545; text-decoration: none; font-weight: bold; margin-top: 10px; display: block; }
    
    /* Algemene icon fix voor Joomla 4/5 */
    [class^="icon-"], [class*=" icon-"] { font-family: "Font Awesome 5 Free", "Font Awesome 6 Free", "icomoon"; font-weight: 900; }
</style>
<form action="<?php echo Uri::base(); ?>index.php?option=com_planjeagenda&view=venue&layout=edit&id=<?php echo (int) $this->item->id; ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

    <div class="row">
        <div class="col-lg-9">
            <?php echo HTMLHelper::_('uitab.startTabSet', 'venueTabs', ['active' => 'details']); ?>

            <!-- TAB 1: Algemene Gegevens -->
            <?php echo HTMLHelper::_('uitab.addTab', 'venueTabs', 'details', 'Details'); ?>
                <div class="card border-top-0 rounded-0">
                    <div class="card-body">
                        <?php echo $this->form->renderFieldset('details'); ?>
                    </div>
                </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <!-- TAB 2: Bijlagen -->
            <?php echo HTMLHelper::_('uitab.addTab', 'venueTabs', 'attachments', 'Bijlagen (' . count($attachments) . ')'); ?>
                <div class="card border-top-0 rounded-0">
                    <div class="card-body">
                        
                        <!-- 1. Overzicht van huidige bestanden -->
                        <div class="attachments-list mb-4">
                            <h5>Huidige Bijlagen</h5>
                            <?php if (!empty($attachments)) : ?>
                                <div class="row">
                                    <?php foreach ($attachments as $attachment) : ?>
                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <div class="border p-2 rounded text-center bg-light shadow-sm">
                                                <?php 
                                                    $filePath = Uri::root() . $attachment->path; 
                                                    $isImage  = in_array($attachment->filetype, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                                                ?>
                                                <div class="img-preview mb-2 d-flex align-items-center justify-content-center bg-white" style="height: 120px; border: 1px solid #ddd;">
                                                    <?php if ($isImage) : ?>
                                                        <img src="<?php echo $filePath; ?>" class="img-fluid" style="max-height: 100%;">
                                                    <?php else : ?>
                                                        <span class="icon-file" style="font-size: 3rem; color: #999;"></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="small text-truncate" title="<?php echo $attachment->filename; ?>">
                                                    <strong><?php echo $attachment->filename; ?></strong>
                                                </div>
                                                <button type="button" class="btn btn-danger btn-sm mt-2 w-100" onclick="removeAttachment(<?php echo $attachment->id; ?>)">
                                                    <span class="icon-trash"></span> Verwijder
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-info">Geen bijlagen gevonden voor deze locatie.</div>
                            <?php endif; ?>
                        </div>

                        <hr />

                        <!-- 2. Dropzone Upload -->
                        <h5>Nieuwe bijlagen uploaden</h5>
                        <div id="pja-dropzone" class="dropzone border-dashed p-5 text-center rounded mb-3" style="background: #f8f9fa; border: 2px dashed #ccc;">
                            <div class="dz-message">
                                <span class="h4">Sleep bestanden hierheen</span><br>
                                <span class="text-muted">of klik om te selecteren</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>

        <!-- Sidebar Kolom -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <?php echo $this->form->renderFieldset('sidebar'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden containers voor Dropzone -->
    <div id="attachments-data-container"></div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
function removeAttachment(id) {
    if (confirm('Weet je zeker dat je deze bijlage wilt verwijderen?')) {
        window.location.href = 'index.php?option=com_planjeagenda&task=venue.removeAttachment&attachment_id=' + id + '&<?php echo HTMLHelper::_('form.token'); ?>=1';
    }
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Zorg dat Dropzone zich niet automatisch koppelt aan alles
    if (typeof Dropzone !== 'undefined') {
        Dropzone.autoDiscover = false;

        // 2. Initialiseer Dropzone op onze div
        var myDropzone = new Dropzone("#pja-dropzone", { 
            url: "index.php", // Wordt niet gebruikt voor de upload zelf
            autoProcessQueue: false,
            addRemoveLinks: true,
            parallelUploads: 10,
            init: function() {
                this.on("addedfile", function(file) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        var base64String = event.target.result;
                        var container = document.getElementById('attachments-data-container');
                        
                        // Maak een wrapper voor dit specifieke bestand
                        var wrapper = document.createElement('div');
                        wrapper.id = 'file-' + file.upload.uuid;

                        // Input voor de Base64 data
                        var inputData = document.createElement('input');
                        inputData.type  = 'hidden';
                        inputData.name  = 'jform[attachments_data][]';
                        inputData.value = base64String;
                        
                        // Input voor de bestandsnaam
                        var inputName = document.createElement('input');
                        inputName.type  = 'hidden';
                        inputName.name  = 'jform[attachments_names][]';
                        inputName.value = file.name;

                        wrapper.appendChild(inputData);
                        wrapper.appendChild(inputName);
                        container.appendChild(wrapper);
                    };
                    reader.readAsDataURL(file);
                });

                // Als een bestand uit de lijst wordt verwijderd, verwijder dan ook de hidden inputs
                this.on("removedfile", function(file) {
                    var wrapper = document.getElementById('file-' + file.upload.uuid);
                    if (wrapper) wrapper.remove();
                });
            }
        });
    } else {
        console.error("Dropzone library niet gevonden! Staat de link naar de JS-file in view.html.php?");
    }
});
</script>