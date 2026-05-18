/**
 * Month Picker Fallback for Joomla 6
 * Converts <input type="month"> to two selects (year + month) 
 * for browsers that don't support native month input.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Feature detection
    const testInput = document.createElement('input');
    testInput.setAttribute('type', 'month');

    // Browser supports native month input → do nothing
    if (testInput.type === 'month') {
        return;
    }

    const monthInputs = document.querySelectorAll('input[type="month"]');

    monthInputs.forEach(function (input) {
        // Hide original input but keep it in the form
        input.style.display = 'none';
        input.type = 'text'; // ensure it's not month type

        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = String(now.getMonth() + 1).padStart(2, '0');

        // Parse existing value
        let selectedYear = '';
        let selectedMonth = '';
        if (input.value && /^\d{4}-\d{2}$/.test(input.value)) {
            [selectedYear, selectedMonth] = input.value.split('-');
        }

        // Create Year Select
        const yearSelect = document.createElement('select');
        yearSelect.className = 'form-select me-2';
        yearSelect.style.minWidth = '100px';

        // Year range: 5 years back to 50 years forward
        for (let y = currentYear - 5; y <= currentYear + 50; y++) {
            const opt = document.createElement('option');
            opt.value = y;
            opt.textContent = y;
            if (y.toString() === selectedYear) opt.selected = true;
            yearSelect.appendChild(opt);
        }

        // Empty option at top
        const emptyYear = document.createElement('option');
        emptyYear.value = '';
        emptyYear.textContent = '----';
        yearSelect.insertBefore(emptyYear, yearSelect.firstChild);

        if (!selectedYear) yearSelect.value = '';

        // Create Month Select
        const monthSelect = document.createElement('select');
        monthSelect.className = 'form-select';
        monthSelect.style.minWidth = '80px';

        for (let m = 1; m <= 12; m++) {
            const opt = document.createElement('option');
            const val = String(m).padStart(2, '0');
            opt.value = val;
            opt.textContent = val;
            if (val === selectedMonth) opt.selected = true;
            monthSelect.appendChild(opt);
        }

        const emptyMonth = document.createElement('option');
        emptyMonth.value = '';
        emptyMonth.textContent = '--';
        monthSelect.insertBefore(emptyMonth, monthSelect.firstChild);

        if (!selectedMonth) monthSelect.value = '';

        // Update hidden input when selection changes
        function updateInput() {
            if (yearSelect.value && monthSelect.value) {
                input.value = yearSelect.value + '-' + monthSelect.value;
            } else {
                input.value = '';
            }
        }

        yearSelect.addEventListener('change', updateInput);
        monthSelect.addEventListener('change', updateInput);

        // Insert selects after the original input
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex align-items-center';
        wrapper.appendChild(yearSelect);
        wrapper.appendChild(monthSelect);

        input.parentNode.insertBefore(wrapper, input.nextSibling);
    });
});