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

    // ---- Tooltip containers ----
    const items = Array.from(document.querySelectorAll('.tooltip-container'));

    // Hide all except optional "keep" element
    function hideAll(keep = null) {
        items.forEach(el => {
            if (el !== keep) {
                el.classList.remove('is-open');
                if (el === document.activeElement) el.blur();
            }
        });
    }

    items.forEach(el => {
        // Open on hover, but ensure only one is active
        el.addEventListener('mouseenter', () => {
            hideAll(el);
            // purely hover should still show via your CSS .tooltip-container:hover .tooltip-content
            // we don't set is-open here to keep hover behavior unchanged
        });

        // Keyboard focus: keep only one open
        el.addEventListener('focus', () => hideAll(el));

        // Click to "pin" (toggle). Only one pinned at a time.
        el.addEventListener('click', (e) => {
            const willOpen = !el.classList.contains('is-open');
            hideAll(willOpen ? el : null);
            el.classList.toggle('is-open', willOpen);
            // Prevent the click from bubbling to the document "outside click" handler
            e.stopPropagation();
        });

        // If you leave with the mouse and it's not pinned (no .is-open), let it close
        el.addEventListener('mouseleave', () => {
            if (!el.classList.contains('is-open') && !el.matches(':focus')) {
                // hover-out: your CSS already hides, nothing needed
            }
        });

        // When focus is lost, unpin
        el.addEventListener('blur', () => el.classList.remove('is-open'));
    });

    // Click outside → close any pinned tooltip
    document.addEventListener('click', () => hideAll());

    // ESC to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideAll();
    });

    const containers = Array.from(document.querySelectorAll('.tooltip-container'));

    function positionTip(container) {
        const tip = container.querySelector('.tooltip-content');
        if (!tip) return;

        // Reset any previous inline transforms/positions
        tip.style.transform = '';
        tip.classList.remove('pos-above', 'pos-below', 'pos-left', 'pos-right');

        // Start with a sensible default (to the right, below)
        tip.classList.add('pos-right', 'pos-below');

        // Wait a frame so the browser can measure with classes applied
        requestAnimationFrame(() => {
            const vw = window.innerWidth;
            const vh = window.innerHeight;
            const tipRect = tip.getBoundingClientRect();
            const contRect = container.getBoundingClientRect();

            // Decide vertical placement
            let vertical = 'below';
            if (tipRect.bottom > vh && contRect.top > tipRect.height) vertical = 'above';
            tip.classList.toggle('pos-below', vertical === 'below');
            tip.classList.toggle('pos-above', vertical === 'above');

            // Re-measure if we changed vertical
            let r = tip.getBoundingClientRect();

            // Decide horizontal placement
            let horizontal = 'right';
            if (r.right > vw && contRect.left > r.width) horizontal = 'left';
            tip.classList.toggle('pos-right', horizontal === 'right');
            tip.classList.toggle('pos-left', horizontal === 'left');

            // Final nudge to keep fully on-screen
            r = tip.getBoundingClientRect();
            let dx = 0, dy = 0;
            if (r.left < 0) dx += -r.left + 8;
            if (r.right > vw) dx += vw - r.right - 8;
            if (r.top < 0) dy += -r.top + 8;
            if (r.bottom > vh) dy += vh - r.bottom - 8;
            if (dx || dy) tip.style.transform = `translate(${dx}px, ${dy}px)`;
        });
    }

    // Position when tip becomes visible (hover/focus/pin)
    containers.forEach(c => {
        c.addEventListener('mouseenter', () => positionTip(c));
        c.addEventListener('focus', () => positionTip(c), true);
        c.addEventListener('click', () => positionTip(c)); // if you support click-to-pin
    });

    // Reposition any open ones on resize/scroll
    const repack = () => {
        document.querySelectorAll('.tooltip-container.is-open, .tooltip-container:hover, .tooltip-container:focus-within')
            .forEach(c => positionTip(c));
    };
    window.addEventListener('resize', repack, { passive: true });
    window.addEventListener('scroll', repack, { passive: true });
});
