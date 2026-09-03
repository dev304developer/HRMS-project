import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Month-navigable dashboard calendar. Receives a { "Y-m-d": [events] } map
// from the server and marks days that carry holidays or meetings.
Alpine.data('hrmsCalendar', (events, todayIso) => {
    const iso = (d) => d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');

    const today = new Date(todayIso + 'T00:00:00');

    return {
        events: events || {},
        cursor: new Date(today.getFullYear(), today.getMonth(), 1),

        get monthLabel() {
            return this.cursor.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        },

        // Flat 7-column grid; leading/trailing nulls pad the first and last weeks.
        get days() {
            const year = this.cursor.getFullYear();
            const month = this.cursor.getMonth();
            const cells = [];

            for (let i = 0; i < new Date(year, month, 1).getDay(); i++) {
                cells.push(null);
            }

            const total = new Date(year, month + 1, 0).getDate();

            for (let day = 1; day <= total; day++) {
                const date = new Date(year, month, day);
                const key = iso(date);
                const evts = this.events[key] || [];
                const isToday = key === todayIso;
                const holiday = evts.some((e) => e.type === 'holiday');

                let classes = 'text-gray-700 hover:bg-gray-100';
                if (isToday) {
                    classes = 'font-semibold text-white';
                } else if (date.getDay() === 0) {
                    classes = 'text-red-500 hover:bg-gray-100';
                }

                let dotColor = '';
                if (evts.length) {
                    dotColor = isToday ? '#ffffff' : holiday ? '#ef4444' : '#2f80ed';
                }

                cells.push({
                    day,
                    classes,
                    dotColor,
                    style: isToday ? 'background-color:#2f80ed;' : '',
                    label: evts.map((e) => e.title).join(', '),
                });
            }

            while (cells.length % 7 !== 0) {
                cells.push(null);
            }

            return cells;
        },

        shift(delta) {
            this.cursor = new Date(this.cursor.getFullYear(), this.cursor.getMonth() + delta, 1);
        },

        goToday() {
            this.cursor = new Date(today.getFullYear(), today.getMonth(), 1);
        },
    };
});


Alpine.start();
