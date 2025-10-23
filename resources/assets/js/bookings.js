document.addEventListener('DOMContentLoaded', function () {
    // ---- Display old bookings: toggle a CSS class on <body> ----
    const oldCb = document.getElementById('filter-old');
    if (oldCb) {
        function syncOldToggle() {
            document.body.classList.toggle('show-old', !!oldCb.checked);
        }
        syncOldToggle();
        oldCb.addEventListener('change', syncOldToggle);
    }

    // ---- Type filters ----
    const typeCheckboxes = Array.from(document.querySelectorAll('input[type="checkbox"][data-filter]'));
    function applyTypeFilters() {
        const allowed = new Set(typeCheckboxes.filter(cb => cb.checked).map(cb => cb.getAttribute('data-filter')));
        document.querySelectorAll('.booking-entry').forEach(el => {
            const kind = el.getAttribute('data-kind') || 'normal';
            el.style.display = allowed.has(kind) ? '' : 'none';
        });
    }
    typeCheckboxes.forEach(cb => cb.addEventListener('change', applyTypeFilters));
    applyTypeFilters();

    // ---- Live clocks + TZ message ----
    function updateTimes() {
        const now = new Date();
        const local = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const utc = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'UTC' });
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;

        const offsetMinutes = -now.getTimezoneOffset(); // + ahead, - behind
        const absMinutes = Math.abs(offsetMinutes);
        const hours = Math.floor(absMinutes / 60);
        const minutes = absMinutes % 60;

        let relation, hint;
        if (offsetMinutes === 0) {
            relation = "the same as Zulu.";
            hint = "No conversion is needed.";
        } else if (offsetMinutes > 0) {
            relation = `${hours}h${minutes ? ` ${minutes}m` : ""} ahead of Zulu.`;
            hint = `To convert any time on Bookings to your local time, add ${hours}h${minutes ? ` ${minutes}m` : ""}.`;
        } else {
            relation = `${hours}h${minutes ? ` ${minutes}m` : ""} behind Zulu.`;
            hint = `To convert any time on Bookings to your local time, subtract ${hours}h${minutes ? ` ${minutes}m` : ""}.`;
        }

        document.querySelectorAll('#local-time').forEach(el => el.textContent = local);
        document.querySelectorAll('#utc-time').forEach(el => el.textContent = utc);
        document.querySelectorAll('#tz-message').forEach(el => el.innerHTML =
            `Your local time (${tz}) is ${relation}<br>${hint}`);
    }
    updateTimes();
    setInterval(updateTimes, 1000);

    // ---- Promote bookings to .is-past as time passes ----
    function rollPastOverTime() {
        const now = new Date();
        document.querySelectorAll('.booking-entry:not(.is-past)').forEach(el => {
            const span = el.querySelector('.booking-time');
            if (!span) return;
            const endISO = span.dataset.end;
            if (!endISO) return;
            const end = new Date(endISO);
            if (!isNaN(end) && end <= now) el.classList.add('is-past');
        });
    }
    rollPastOverTime();
    setInterval(rollPastOverTime, 60000);

    // ---- Local time toggle ----
    const toggleLocalTime = document.getElementById('toggle-localtime');
    if (toggleLocalTime) {
        function updateBookingTimes() {
            const useLocal = toggleLocalTime.checked;
            document.querySelectorAll('.booking-time').forEach(span => {
                const startISO = span.dataset.start;
                const endISO = span.dataset.end;
                if (!startISO || !endISO) return;

                const startUtc = new Date(startISO);
                const endUtc = new Date(endISO);
                if (isNaN(startUtc) || isNaN(endUtc)) return;

                if (useLocal) {
                    const startLocal = startUtc.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const endLocal = endUtc.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const tzName = Intl.DateTimeFormat().resolvedOptions().timeZone;
                    span.textContent = `${startLocal} - ${endLocal} (${tzName})`;
                } else {
                    const startZulu = startUtc.toISOString().substr(11, 5);
                    const endZulu = endUtc.toISOString().substr(11, 5);
                    span.textContent = `${startZulu}z - ${endZulu}z`;
                }
            });
        }
        updateBookingTimes();
        toggleLocalTime.addEventListener('change', updateBookingTimes);
    }
});
