/**
 * Plan Je Agenda - Event Edit
 * Handles registration field visibility, max places validation, and map/comments toggles.
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // ── Registration field visibility ──────────────────────────────────────
    const registraSelect = document.getElementById('jform_registra');
    if (registraSelect) {
        const registraPanels = registraSelect.closest('.adminformlist')
            ?.querySelectorAll('li:not(:first-child)');

        function updateRegistraVisibility() {
            const hidden = parseInt(registraSelect.value) === 0;
            registraPanels?.forEach(li => li.style.display = hidden ? 'none' : '');
        }

        registraSelect.addEventListener('change', updateRegistraVisibility);
        updateRegistraVisibility();
    }

    // ── Max places / booked validation ────────────────────────────────────
    const inputs = {
        min:      document.getElementById('jform_minbookeduser'),
        max:      document.getElementById('jform_maxbookeduser'),
        places:   document.getElementById('jform_maxplaces'),
        reserved: document.getElementById('jform_reservedplaces'),
        avail:    document.getElementById('availableplaces'),
        booked:   document.getElementById('event-booked'),
    };

    function clampPlaces() {
        const min      = parseInt(inputs.min?.value)      || 0;
        const max      = parseInt(inputs.max?.value)      || 0;
        const places   = parseInt(inputs.places?.value)   || 0;
        const reserved = parseInt(inputs.reserved?.value) || 0;
        const booked   = parseInt(inputs.booked?.value)   || 0;

        if (inputs.min && places && min > places) inputs.min.value = places;
        if (inputs.max && places && max > places) inputs.max.value = places;
        if (inputs.min && inputs.max && min > max) inputs.min.value = max;
        if (inputs.reserved && places && reserved > places) inputs.reserved.value = places;

        if (inputs.avail) {
            inputs.avail.value = places ? Math.max(0, places - booked - reserved) : 0;
        }
    }

    [inputs.min, inputs.max, inputs.places, inputs.reserved].forEach(el => {
        el?.addEventListener('change', clampPlaces);
        el?.addEventListener('input',  clampPlaces);
    });
    clampPlaces();

    // ── Map toggle ────────────────────────────────────────────────────────
    const mapSelect = document.getElementById('jform_attribs_event_show_mapserv');
    if (mapSelect) {
        function updateMap() {
            const on = ['1','2'].includes(mapSelect.value);
            ['eventmap1','eventmap2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = on ? '' : 'none';
            });
        }
        mapSelect.addEventListener('change', updateMap);
        updateMap();
    }

    // ── Comments toggle ───────────────────────────────────────────────────
    const commSelect = document.getElementById('jform_attribs_event_comunsolution');
    if (commSelect) {
        function updateComm() {
            const el = document.getElementById('comm1');
            if (el) el.style.display = commSelect.value === '1' ? '' : 'none';
        }
        commSelect.addEventListener('change', updateComm);
        updateComm();
    }

});
