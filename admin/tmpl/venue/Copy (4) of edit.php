<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// Essentiële Joomla scripts
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tab'); // Belangrijk voor de werking van de tabs!

$attachments = $this->attachments ?? [];

$doc = \Joomla\CMS\Factory::getDocument();
$doc->addScript('https://cdn.jsdelivr.net/npm/sweetalert2@11');

// Instellingen die je later makkelijk kunt aanpassen (of uit een config-bestand kunt halen)
$dzConfig = [
    'max_width'   => 1200, // Maximale breedte in pixels
    'quality'     => 0.8,    // Kwaliteit (0.1 tot 1.0)
    'max_files'   => 10,     // Maximaal aantal bestanden
    'accepted'    => 'image/*', // Welke bestanden zijn toegestaan
    'parallel'    => 5       // Hoeveel tegelijk verwerken
];
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


/* De container van de preview */
    .dropzone .dz-preview {
        margin: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        background: #fdfdfd; /* De achtergrond van het hele kaartje */
    }

    /* De bestandsnaam boven het plaatje */
    .dropzone .dz-preview .dz-details {
        background-color: #333 !important; /* Maak de achtergrond donker */
        color: #fff !important;            /* Tekst wit voor contrast */
        opacity: 0.9;
        padding: 5px !important;
        border-radius: 4px;
        font-size: 11px;
    }

    /* Zorg dat de naam niet over het plaatje heen valt (indien gewenst) */
    .dropzone .dz-preview .dz-details .dz-filename {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Het plaatje zelf binnen de preview */
    .dropzone .dz-preview .dz-image {
        border-radius: 4px !important;
        width: 120px !important;
        height: 120px !important;
    }

    /* Verwijder-link styling */
    .dropzone .dz-preview .dz-remove {
        color: #dc3545 !important;
        font-size: 12px;
        text-decoration: none;
        margin-top: 5px;
        display: block;
        font-weight: bold;
    }
    
    .dropzone .dz-preview .dz-remove:hover {
        text-decoration: underline;
    }
/* 1. Zorg dat de preview container hoog genoeg is voor tekst onder de afbeelding */
    .dropzone .dz-preview {
        margin: 15px;
        min-height: 180px; /* Extra ruimte voor tekst onderaan */
        background: #fff !important;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* 2. De afbeelding container */
    .dropzone .dz-preview .dz-image {
        border-radius: 4px !important;
        width: 120px !important;
        height: 120px !important;
        margin-bottom: 40px; /* Creëer ruimte voor de tekst die we naar beneden duwen */
        z-index: 1;
    }

    /* 3. De details (Naam en Grootte) naar beneden verplaatsen */
    .dropzone .dz-preview .dz-details {
        position: absolute;
        top: 130px !important; /* Plaats het net onder de 120px hoge afbeelding */
        left: 0;
        width: 100%;
        background: transparent !important; /* Weg met die zwarte balk */
        color: #333 !important;            /* Donkere tekst op witte achtergrond */
        opacity: 1 !important;             /* Altijd zichtbaar, niet alleen bij hover */
        padding: 0 5px !important;
        text-align: center;
        line-height: 1.2;
        transition: none !important;
    }

    /* 4. Specifieke styling voor de bestandsnaam */
    .dropzone .dz-preview .dz-details .dz-filename {
        display: block;
        margin-bottom: 2px;
    }

    .dropzone .dz-preview .dz-details .dz-filename span {
        border: none !important;
        background: transparent !important;
        font-weight: bold;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    /* 5. De bestandsgrootte */
    .dropzone .dz-preview .dz-details .dz-size {
        font-size: 10px;
        color: #777;
        margin-bottom: 0 !important;
    }

    /* 6. Verwijderknop ("Remove file") netjes onderaan plaatsen */
    .dropzone .dz-preview .dz-remove {
        position: absolute;
        bottom: 5px;
        left: 0;
        width: 100%;
        text-align: center;
        color: #d9534f !important;
        text-decoration: none;
        font-size: 11px;
        font-weight: bold;
        z-index: 10;
    }

    /* Verberg de vinkjes en errors die soms over de tekst heen vallen op hover */
    .dropzone .dz-preview .dz-success-mark, 
    .dropzone .dz-preview .dz-error-mark {
        top: 30% !important;
    }
    
    /* 1. Verberg de progress-balk volledig (omdat we Base64 gebruiken is hij direct 100%) */
    .dropzone .dz-preview .dz-progress {
        display: none !important;
    }

    /* 2. Mocht je hem toch willen zien, gebruik dan deze styling in plaats van 'display: none': */
    /*
    .dropzone .dz-preview .dz-progress {
        top: 125px !important; 
        left: 5% !important;
        width: 90% !important;
        height: 5px !important;
        background: rgba(0,0,0,0.1) !important;
        border-radius: 10px;
        overflow: hidden;
    }
    .dropzone .dz-preview .dz-progress .dz-upload {
        background: #28a745 !important;
    }
    */

    /* 3. Zorg dat de afbeelding niet lichter wordt (opacity) bij het laden */
    .dropzone .dz-preview.dz-processing .dz-image img {
        opacity: 1 !important;
        filter: none !important;
    }

    /* 4. Fix voor de vinkjes (success/error marks) die ook over het plaatje kunnen zweven */
    .dropzone .dz-preview .dz-success-mark, 
    .dropzone .dz-preview .dz-error-mark {
        display: none !important; /* Verberg deze, ze staan vaak lelijk in de weg */
    }

/* 1. Voorkom dat de afbeelding lichter wordt als je eroverheen gaat (hover) */
    .dropzone .dz-preview:hover .dz-image img {
        transform: none !important; /* Voorkom inzoomen/schalen */
        filter: none !important;    /* Voorkom blur of kleurverandering */
        opacity: 1 !important;      /* Houd het plaatje 100% zichtbaar */
    }

    /* 2. Verwijder de zwarte overlay die Dropzone standaard over het plaatje tekent op hover */
    .dropzone .dz-preview .dz-image:hover {
        background: transparent !important;
    }

    /* 3. Zorg dat de afbeelding container zelf niet van kleur verandert */
    .dropzone .dz-preview:hover .dz-image {
        background: transparent !important;
    }

    /* 4. Als je juist een subtiel effect wilt in plaats van faden, 
       kun je een dun randje toevoegen als visuele feedback: */
    .dropzone .dz-preview:hover {
        border-color: #007bff !important; /* Blauw randje om het hele kaartje op hover */
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.2s ease-in-out;
    }
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
            
            <!-- 1. Dropzone Upload (NU BOVENAAN) -->
            <div class="upload-section mb-4">
                <h5>Nieuwe bijlagen uploaden</h5>
                <div id="pja-dropzone" class="dropzone border-dashed p-5 text-center rounded mb-3" 
                data-max-width="<?php echo $dzConfig['max_width']; ?>" 
                data-quality="<?php echo $dzConfig['quality']; ?>"
     			data-max-files="<?php echo $dzConfig['max_files']; ?>"
                style="background: #f8f9fa; border: 2px dashed #007bff;">
                    <div class="dz-message">
                        <span class="h4 text-primary">Sleep bestanden hierheen</span><br>
                        <span class="text-muted">of klik om te selecteren</span>
                    </div>
                </div>
            </div>
            <hr class="my-5" />

            <!-- 2. Overzicht van huidige bestanden (NU ONDERAAN) -->
            <div class="attachments-list">
                <h5 class="mb-3">Reeds gekoppelde bijlagen</h5>
                <?php if (!empty($attachments)) : ?>
                    <div class="row">
                        <?php foreach ($attachments as $attachment) : ?>
                            <div class="col-md-3 col-sm-6 mb-3" id="attachment-<?php echo $attachment->id; ?>">
                                <div class="border p-2 rounded text-center bg-light shadow-sm h-100 d-flex flex-column">
                                    <?php 
                                        $filePath = Uri::root() . $attachment->path; 
                                        $isImage  = in_array($attachment->filetype, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                                    ?>
                                    <div class="img-preview mb-2 d-flex align-items-center justify-content-center bg-white" style="height: 100px; border: 1px solid #ddd; overflow: hidden;">
                                        <?php if ($isImage) : ?>
                                            <img src="<?php echo $filePath; ?>" class="img-fluid" style="max-height: 100%;">
                                        <?php else : ?>
                                            <span class="icon-file text-muted" style="font-size: 2.5rem;"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-truncate mb-auto" title="<?php echo $attachment->filename; ?>">
                                        <strong><?php echo $attachment->filename; ?></strong>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm mt-3 w-100" onclick="removeAttachment(<?php echo $attachment->id; ?>)">
                                        <span class="icon-trash"></span> Verwijder
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="alert alert-light border">Nog geen bijlagen aanwezig voor deze locatie.</div>
                <?php endif; ?>
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
    
    <div id="pja-modal" onclick="this.style.display='none'">
    <img id="pja-modal-img" src="" alt="Preview">
</div>
</form>

<style>
    /* Dropzone basis styling */
    .dropzone { 
        border: 2px dashed #007bff !important; 
        background: #f8f9fa !important; 
        border-radius: 8px; 
        min-height: 200px; 
        transition: all 0.2s ease;
    }
    .dropzone:hover { border-color: #0056b3 !important; background: #f1f7ff !important; }

    /* Preview kaartjes styling */
    .dropzone .dz-preview {
        margin: 15px;
        min-height: 180px;
        background: #fff !important;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        vertical-align: top;
    }

    /* Thumbnail fix & Zoom cursor */
    .dropzone .dz-preview .dz-image {
        border-radius: 4px !important;
        width: 120px !important;
        height: 120px !important;
        margin-bottom: 40px;
        cursor: zoom-in;
        z-index: 1;
    }

    /* Voorkom faden en effecten op hover */
    .dropzone .dz-preview:hover .dz-image img {
        transform: none !important;
        filter: none !important;
        opacity: 1 !important;
    }

    /* Bestandsnaam en grootte onder het plaatje */
    .dropzone .dz-preview .dz-details {
        position: absolute;
        top: 130px !important;
        left: 0;
        width: 100%;
        background: transparent !important;
        color: #333 !important;
        opacity: 1 !important;
        padding: 0 5px !important;
        text-align: center;
        line-height: 1.2;
    }

    .dropzone .dz-preview .dz-details .dz-filename span {
        background: transparent !important;
        border: none !important;
        font-weight: bold;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dropzone .dz-preview .dz-details .dz-size {
        font-size: 11px;
        color: #777;
    }

    /* Verwijder voortgangsbalk en vinkjes voor een schone look */
    .dropzone .dz-preview .dz-progress,
    .dropzone .dz-preview .dz-success-mark,
    .dropzone .dz-preview .dz-error-mark {
        display: none !important;
    }

    /* Verwijder-knop styling */
    .dropzone .dz-preview .dz-remove {
        position: absolute;
        bottom: 8px;
        left: 0;
        width: 100%;
        text-align: center;
        color: #d9534f !important;
        font-weight: bold;
        font-size: 11px;
        text-decoration: none;
        z-index: 5;
    }
    
    /* De Modal Achtergrond */
#pja-modal {
    display: none; 
    position: fixed; 
    z-index: 9999; 
    left: 0; top: 0; 
    width: 100%; height: 100%; 
    background-color: rgba(0,0,0,0.9);
    align-items: center; 
    justify-content: center;
    cursor: pointer;
}

/* De Afbeelding in de Modal */
#pja-modal img {
    max-width: 90%;
    max-height: 90%;
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
    border-radius: 4px;
}
/* De 'Verwijder' link groter en duidelijker maken */
.dropzone .dz-preview .dz-remove {
    display: block;
    margin-top: 10px;
    padding: 8px 10px;
    font-size: 10px;             /* Iets groter lettertype */
    font-weight: bold;
    color: #fff !important;      /* Witte tekst */
    background-color: #d9534f;   /* Rood/Gevaar kleur */
    border-radius: 4px;
    text-decoration: none !important;
    transition: background 0.2s ease;
    cursor: pointer;
    z-index: 10;                 /* Zorg dat hij altijd bovenop ligt */
}

/* Hover effect voor de verwijderknop */
.dropzone .dz-preview .dz-remove:hover {
    background-color: #c9302c;   /* Donkerder rood bij overgaan */
    color: #fff !important;
}

/* De container van het kaartje iets hoger maken om ruimte te geven aan de grotere knop */
.dropzone .dz-preview {
    min-height: 210px !important; 
}
/* De cursor veranderen naar een vergrootglas over de afbeelding */
.dropzone .dz-preview .dz-image {
    cursor: zoom-in !important;
}

/* Optioneel: Een lichte schaduw en schaling voor extra feedback */
.dropzone .dz-preview .dz-image:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: box-shadow 0.2s ease-in-out;
}

/* Als je de cursor over de hele kaart klikbaar wilt maken (behalve de verwijder-knop) */
.dropzone .dz-preview {
    cursor: zoom-in;
}

/* Zorg dat de cursor boven de verwijder-knop wel gewoon een handje (pointer) is */
.dropzone .dz-preview .dz-remove {
    cursor: pointer !important;
}
.dropzone .dz-preview .dz-custom-name {
    position: relative;
    z-index: 20; /* Zorg dat het bovenop het kaartje ligt */
    margin-top: -35px; /* Duw het omhoog over de witruimte */
    padding: 0 5px;
}

.dropzone .dz-preview .dz-custom-name input {
    border: 1px solid #ced4da;
    text-align: center;
}

/* Zorg dat de preview container iets hoger is voor de input */
.dropzone .dz-preview {
    min-height: 230px !important;
}
/* Standaard icoon voor documenten in Dropzone */
.dz-file-preview .dz-image img {
    display: none; /* Verberg de lege img tag */
}

.dz-file-preview .dz-image {
    background: #f8f9fa url('pad/naar/document-icon.png') no-repeat center !important;
    background-size: 50% !important;
}

/* Alleen de afbeelding tonen als het echt een image is */
.dz-image-preview .dz-image img {
    display: block;
}
/* Styling voor de previews van documenten */
.dropzone .dz-preview.dz-file-preview .dz-image {
    background: #eee;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Voeg eventueel een icoontje toe via een pseudo-element voor documenten */
.dropzone .dz-preview.dz-file-preview:not(.dz-image-preview) .dz-image:before {
    content: "📄"; /* Of een FontAwesome icon */
    font-size: 40px;
    opacity: 0.5;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dzElement = document.getElementById('pja-dropzone');
    
    if (dzElement && typeof Dropzone !== 'undefined') {
        Dropzone.autoDiscover = false;

        var maxWidth = parseInt(dzElement.getAttribute('data-max-width')) || 1200;
        var quality  = parseFloat(dzElement.getAttribute('data-quality')) || 0.8;

        var myDropzone = new Dropzone("#pja-dropzone", { 
            url: "index.php",
            autoProcessQueue: false,
            addRemoveLinks: false, 
            resizeWidth: maxWidth,          
            resizeQuality: quality,         
            
            previewTemplate: `
                <div class="dz-preview dz-file-preview">
                    <div class="dz-image" style="cursor: pointer;"><img data-dz-thumbnail /></div>
                    <div class="dz-details">
                        <div class="dz-size"><span data-dz-size></span></div>
                        <div class="dz-filename"><span data-dz-name></span></div>
                    </div>
                    <div class="dz-custom-name mt-2" style="position: relative; z-index: 20;">
                        <input type="text" class="form-control form-control-sm custom-file-name" 
                               placeholder="Naam voor bijlage..." 
                               style="font-size: 11px; text-align: center;">
                    </div>
                    <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                    <a class="dz-remove" href="javascript:undefined;" data-dz-remove 
                       style="display: block; text-align: center; margin-top: 5px; color: #dc3545; font-size: 12px; text-decoration: none;">
                       <i class="fas fa-trash"></i> Verwijder
                    </a>
                </div>
            `,
            
            init: function() {
                // 1. Bestand toegevoegd
                this.on("addedfile", function(file) {
                    // Modal trigger
                    file.previewElement.querySelector('.dz-image').addEventListener("click", function() {
                        if (file.type.match(/image.*/)) {
                            var imageData = file.verkleindeData || file.dataURL;
                            if (imageData) {
                                document.getElementById('pja-modal-img').src = imageData;
                                document.getElementById('pja-modal').style.display = 'flex';
                            }
                        }
                    });

                    // Naam input invullen
                    const nameInput = file.previewElement.querySelector('.custom-file-name');
                    nameInput.value = file.name.split('.').slice(0, -1).join('.');
                    
                    nameInput.addEventListener('input', function() {
                        updateHiddenInputs(file, file.verkleindeData || file.documentBase64);
                    });

                    // Verwerking van documenten
                    if (!file.type.match(/image.*/)) {
                        var reader = new FileReader();
                        reader.onload = function(event) {
                            file.documentBase64 = event.target.result;
                            updateHiddenInputs(file, file.documentBase64);
                        };
                        reader.readAsDataURL(file);
                    }
                });

                // 2. Thumbnail klaar (voor afbeeldingen)
                this.on("thumbnail", function(file, dataUrl) {
                    file.verkleindeData = dataUrl;
                    updateHiddenInputs(file, dataUrl);
                });

                // 3. Direct verwijderen (geen pop-up)
                this.on("removedfile", function(file) {
                    var wrapper = document.getElementById('file-' + file.upload.uuid);
                    if (wrapper) wrapper.remove();
                });

                function updateHiddenInputs(file, dataUrl) {
                    var container = document.getElementById('attachments-data-container');
                    var oldWrapper = document.getElementById('file-' + file.upload.uuid);
                    if (oldWrapper) oldWrapper.remove();

                    const customName = file.previewElement.querySelector('.custom-file-name').value;
                    const finalName = customName.trim() !== "" ? customName : file.name;
                    const finalData = dataUrl || "";

                    if (finalData !== "") {
                        var wrapper = document.createElement('div');
                        wrapper.id = 'file-' + file.upload.uuid;
                        wrapper.innerHTML = `
                            <input type="hidden" name="jform[attachments_data][]" value="${finalData}">
                            <input type="hidden" name="jform[attachments_names][]" value="${finalName}">
                        `;
                        container.appendChild(wrapper);
                    }
                }
            }
        });
    }
});

function removeAttachment(attachmentId) {
    Swal.fire({
        title: 'Bijlage verwijderen?',
        text: "Dit kan niet ongedaan worden gemaakt!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ja, weg ermee!',
        cancelButtonText: 'Annuleren',
        background: '#fff',
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) {
            // Hier start je AJAX logic
            const url = 'index.php?option=com_planjeagenda&task=venue.ajaxRemoveAttachment&format=json';
            const token = '<?php echo \Joomla\CMS\Session\Session::getFormToken(); ?>';
            let formData = new FormData();
            formData.append('id', attachmentId);
            formData.append(token, '1');

            fetch(url, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Verwijderd!', 'Het bestand is uit de database.', 'success');
                    const element = document.getElementById('attachment-' + attachmentId);
                    if (element) {
                        element.style.transition = 'all 0.5s';
                        element.style.transform = 'scale(0)';
                        setTimeout(() => element.remove(), 500);
                    }
                    
 // 2. Update het getal in de Tab-titel (Taalonafhankelijk)
// We zoeken de tab die naar de ID 'attachments' wijst
const tabLink = document.querySelector('button[aria-controls="attachments"], a[aria-controls="attachments"]');

if (tabLink) {
    // We pakken de tekst (bijv. "Bijlagen (2)")
    let currentText = tabLink.innerText;
    
    // We zoeken naar het getal tussen de haakjes
    const match = currentText.match(/\((\d+)\)/);
    
    if (match && match[1]) {
        let currentCount = parseInt(match[1]);
        let newCount = Math.max(0, currentCount - 1);
        
        // Vervang de oude tekst door de nieuwe tekst met het bijgewerkte getal
        tabLink.innerText = currentText.replace(`(${currentCount})`, `(${newCount})`);
        
        // Optioneel: log even naar de console om te zien of hij hem raakt
        console.log('Tab bijgewerkt van ' + currentCount + ' naar ' + newCount);
    } else {
        console.log('Geen getal gevonden in tab tekst: ' + currentText);
    }
} else {
    console.log('Tab link met aria-controls="attachments" niet gevonden');
}
                    
                    
                }
            });
        }
    });
}
</script>