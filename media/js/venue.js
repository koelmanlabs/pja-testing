Dropzone.autoDiscover = false;

jQuery(function($) {
    var myDropzone;
    var form = document.getElementById('adminForm');

    if ($('#venue-uploader').length > 0) {
        myDropzone = new Dropzone("#venue-uploader", { 
            url: "index.php",
            autoProcessQueue: false,
            uploadMultiple: true,
            paramName: "attachments", // We gebruiken een tijdelijke naam
            addRemoveLinks: true,
            dictRemoveFile: "<button type='button' class='btn btn-danger btn-sm mt-2 dz-remove-btn'>Verwijder</button>",
            init: function() {
                var self = this;
                this.on("addedfile", function(file) {
                    file.previewElement.querySelector(".dz-remove-btn").addEventListener("click", function(e) {
                        e.preventDefault();
                        self.removeFile(file);
                    });
                });
            }
        });
    }

    // Joomla Toolbar override
    Joomla.submitbutton = function(task) {
        if (task === 'venue.cancel') {
            Joomla.submitform(task, form);
            return;
        }

        if (document.formvalidator && !document.formvalidator.isValid(form)) {
            alert('Controleer de velden.');
            return false;
        }

        // --- DE KRUKS: Bestanden omzetten naar form-data ---
        if (myDropzone && myDropzone.getQueuedFiles().length > 0) {
            var promises = myDropzone.getQueuedFiles().map(function(file) {
                return new Promise(function(resolve) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        // Maak een hidden input met de base64 data van het plaatje
                        $('#dropzone-fields-container').append(
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'jform[attachments_data][]',
                                value: event.target.result
                            })
                        );
                        // Stuur ook de bestandsnaam mee
                        $('#dropzone-fields-container').append(
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'jform[attachments_names][]',
                                value: file.name
                            })
                        );
                        resolve();
                    };
                    reader.readAsDataURL(file);
                });
            });

            // Wacht tot alle bestanden zijn omgezet, dan pas submitten
            Promise.all(promises).then(function() {
                Joomla.submitform(task, form);
            });
        } else {
            Joomla.submitform(task, form);
        }
    };
});