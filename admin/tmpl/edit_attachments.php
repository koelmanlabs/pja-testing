<style>
    .upload-card {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        margin-top: 1.5rem;
        background-color: #fff;
    }
    .upload-card-header {
        padding: 0.75rem 1.25rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #ced4da;
        font-weight: bold;
    }
    .upload-card-body {
        padding: 1.25rem;
    }
    .current-attachments-list {
        margin-bottom: 1.5rem;
    }
    .attachment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0.75rem;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
    }
    .map-container {
        width: 100%;
        height: 400px;
        margin-top: 1rem;
        border: 1px solid #ccc;
    }
    .geo-details {
        background: #f1f1f1;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
    }
</style>

<div class="row">
    <div class="col-md-8">
        <!-- Bestaande Formulier Velden -->
        <fieldset class="form-horizontal">
            <legend>Venue Details</legend>
            <?php echo $this->form->renderFieldset('details'); ?>
        </fieldset>

        <!-- GEOMAP SECTIE -->
        <fieldset class="form-horizontal">
            <legend>Locatie & Kaart</legend>
            <div class="control-group">
                <div class="control-label">
                    <label>Zoek Adres:</label>
                </div>
                <div class="controls">
                    <input type="text" id="geocomplete" class="form-control" placeholder="Typ een adres of bedrijfsnaam..." style="width: 100%;" />
                </div>
            </div>

            <div class="map_canvas map-container"></div>

            <div class="geo-details">
                <div class="row">
                    <div class="col-md-6">
                        <label>Lat:</label> <input name="jform[lat]" geo-data="lat" type="text" value="<?php echo $this->item->lat; ?>" readonly />
                    </div>
                    <div class="col-md-6">
                        <label>Lng:</label> <input name="jform[lng]" geo-data="lng" type="text" value="<?php echo $this->item->lng; ?>" readonly />
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <div class="col-md-4">
        <!-- BIJLAGEN SECTIE (Modernized with FilePond) -->
        <div class="upload-card">
            <div class="upload-card-header">
                <span class="icon-paperclip"></span> Bijlagen & Media
            </div>
            <div class="upload-card-body">
                
                <!-- Lijst met huidige bestanden -->
                <div class="current-attachments-list">
                    <h6>Huidige Bijlagen</h6>
                    <div id="current-attachments-container">
                        <?php if (!empty($this->item->attachments)) : ?>
                            <?php foreach ($this->item->attachments as $attach) : ?>
                                <div class="attachment-item" id="attach-row-<?php echo $attach->id; ?>">
                                    <span>
                                        <i class="icon-file"></i> <?php echo htmlspecialchars($attach->filename); ?>
                                    </span>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-existing-attachment" data-id="<?php echo $attach->id; ?>">
                                        <i class="icon-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="text-muted small italic">Geen bijlagen gevonden.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <hr />

                <!-- FilePond Upload Zone -->
                <h6>Nieuwe Bestanden</h6>
                <input type="file" class="filepond" name="jform[attachments][]" multiple data-max-file-size="3MB">
                <small class="text-muted">Sleep bestanden hierheen of klik om te bladeren.</small>

            </div>
        </div>
    </div>
</div>

<script>
    // Initialisatie van scripts
    jQuery(function($) {
        
        // 1. GEOMAP LOGICA
        var $geoInput = $("#geocomplete");
        if ($geoInput.length && typeof google !== 'undefined') {
            
            // Haal initiële locatie op (indien aanwezig in PHP variabele $location)
            var initialLoc = <?php echo json_encode(!empty($location) ? $location : ''); ?>;
            
            var geoOptions = {
                map: ".map_canvas",
                details: "form",
                detailsAttribute: "geo-data",
                types: ['establishment', 'geocode'],
                mapOptions: { 
                    zoom: 16, 
                    mapTypeId: google.maps.MapTypeId.HYBRID 
                },
                markerOptions: { draggable: true }
            };

            if (initialLoc.trim() !== "") {
                geoOptions.location = initialLoc;
            }

            $geoInput.geocomplete(geoOptions);
        }

        // 2. FILEPOND LOGICA
        const pondElement = document.querySelector('.filepond');
        if (pondElement && typeof FilePond !== 'undefined') {
            FilePond.create(pondElement, {
                labelIdle: 'Sleep bestanden of <span class="filepond--label-action">Blader</span>',
                allowMultiple: true,
                storeAsFile: true,
                credits: false
            });
        }

        // 3. AJAX VERWIJDEREN LOGICA
        $('.remove-existing-attachment').on('click', function() {
            var btn = $(this);
            var id = btn.data('id');
            var token = Joomla.getOptions('csrf.token');

            if (confirm('Weet u zeker dat u dit bestand wilt verwijderen?')) {
                btn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i>');

                $.ajax({
                    url: 'index.php?option=com_planjeagenda&task=venue.ajaxRemoveAttachment&format=json',
                    method: 'POST',
                    data: {
                        id: id,
                        [token]: 1
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#attach-row-' + id).slideUp();
                        } else {
                            alert('Fout: ' + (response.message || 'Kon niet verwijderen'));
                            btn.prop('disabled', false).html('<i class="icon-trash"></i>');
                        }
                    },
                    error: function() {
                        alert('Netwerkfout bij verwijderen');
                        btn.prop('disabled', false).html('<i class="icon-trash"></i>');
                    }
                });
            }
        });
    });
</script>