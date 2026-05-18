document.addEventListener('DOMContentLoaded', () => {

    const calendarEl = document.getElementById('pja-calendar');

    if (!calendarEl) {
        return;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: calendarEl.dataset.initialView || 'dayGridMonth',

        locale: calendarEl.dataset.locale || 'nl',

        firstDay: 1,

        height: 'auto',

        nowIndicator: true,

        navLinks: true,

        editable: false,

        selectable: false,

        dayMaxEvents: true,

        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },

        events: {
            url: calendarEl.dataset.eventsUrl,
            method: 'GET',
            failure() {
                console.error('Calendar events konden niet geladen worden');
            }
        },

        loading(isLoading) {

            const loader = document.getElementById('pja-cal-loading');

            if (!loader) {
                return;
            }

            loader.style.display = isLoading ? 'block' : 'none';
        }
    });

    calendar.render();
});