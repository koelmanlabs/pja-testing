document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialiseer FilePond
    const inputElement = document.querySelector('.filepond');
    if (inputElement) {
        const pond = FilePond.create(inputElement, {
            labelIdle: 'Sleep bestanden hierheen of <span class="filepond--label-action">Blader</span>',
            allowMultiple: true,
            storeAsFile: true, // Cruciaal: zorgt dat bestanden als normale $_FILES worden verstuurd
            credits: false
        });
    }

    // 2. AJAX Verwijderen van bestaande bijlagen (jQuery is meestal al geladen in Joomla)
    jQuery('.remove-existing').on('click', function() {
        const btn = jQuery(this);
        const id = btn.data('id');
        const token = document.querySelector('input[name="' + Joomla.getOptions('csrf.token', '') + '"]');

        if (confirm('Bestand definitief verwijderen?')) {
            jQuery.ajax({
                url: 'index.php?option=com_planjeagenda&task=venue.ajaxRemoveAttachment&format=json',
                method: 'POST',
                data: {
                    id: id,
                    [Joomla.getOptions('csrf.token')]: 1
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#attach-container-' + id).slideUp();
                    } else {
                        alert(response.message || 'Fout bij verwijderen');
                    }
                }
            });
        }
    });
});