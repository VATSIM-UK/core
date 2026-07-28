import collapse from '@alpinejs/collapse';

// Registers on the Alpine instance bundled with Livewire (exposed as
// window.Alpine). Using the `alpine:init` hook guarantees the plugin and
// component are registered before Livewire calls Alpine.start().
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(collapse);

    window.Alpine.data('bookingsTimeline', (config) => ({
        selectedDate: config.selectedDate,
        isAuthenticated: config.isAuthenticated,
        nowMinutes: config.nowMinutes,
        isToday: config.isToday,
        scale: config.scale,
        dragging: null,

        minToPct(minutes) {
            const m = Math.max(0, Math.min(1440, Math.round(minutes)));
            return this.scale[m] || 0;
        },

        minWidth(fromMin, toMin) {
            const f = Math.max(0, Math.min(1440, Math.round(fromMin)));
            const t = Math.max(0, Math.min(1440, Math.round(toMin)));
            return Math.max(0.3, this.minToPct(t) - this.minToPct(f));
        },

        nowPct() {
            return this.minToPct(this.nowMinutes);
        },

        snapToSlot(minutes) {
            return Math.floor(minutes / 15) * 15;
        },

        minuteToTime(minutes) {
            const h = Math.floor(minutes / 60) % 24;
            const m = minutes % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
        },

        getPosFromEvent(event, bar) {
            const rect = bar.getBoundingClientRect();
            const x = event.clientX - rect.left;
            return Math.max(0, Math.min(1, x / rect.width));
        },

        pctToMinutes(pct) {
            const scaleArr = this.scale;
            const target = pct * 100;
            let best = 0;
            let bestDist = Infinity;
            for (let i = 0; i <= 1440; i++) {
                const d = Math.abs(scaleArr[i] - target);
                if (d < bestDist) {
                    bestDist = d;
                    best = i;
                }
            }
            return this.snapToSlot(best);
        },

        addDay(dateString) {
            const dt = new Date(dateString + 'T00:00:00');
            dt.setDate(dt.getDate() + 1);
            return dt.toISOString().split('T')[0];
        },

        handleTimelineMouseDown(event, pos) {
            if (!this.isAuthenticated) return;
            if (event.target.closest('[data-booking-block]')) return;
            event.preventDefault();
            const bar = event.currentTarget;
            const pct = this.getPosFromEvent(event, bar);
            const startMinutes = this.pctToMinutes(pct);
            this.dragging = {
                pos,
                bar,
                anchorMinutes: startMinutes,
                startMinutes,
                currentMinutes: startMinutes + 15,
                startPct: this.minToPct(startMinutes),
            };
            this._boundDragMove = this._boundDragMove || this._dragMove.bind(this);
            this._boundDragEnd = this._boundDragEnd || this._dragEnd.bind(this);
            document.addEventListener('mousemove', this._boundDragMove);
            document.addEventListener('mouseup', this._boundDragEnd);
        },

        _dragMove(e) {
            if (!this.dragging) return;
            const pct = this.getPosFromEvent(e, this.dragging.bar);
            const pointer = this.pctToMinutes(pct);
            const anchor = this.dragging.anchorMinutes;
            let start = Math.min(anchor, pointer);
            let end = Math.max(anchor, pointer);
            // Keep at least one 15-minute slot, growing away from the anchor.
            if (end - start < 15) {
                if (pointer < anchor) {
                    start = end - 15;
                } else {
                    end = start + 15;
                }
            }
            this.dragging.startMinutes = start;
            this.dragging.currentMinutes = end;
        },

        _dragEnd() {
            if (!this.dragging) return;
            document.removeEventListener('mousemove', this._boundDragMove);
            document.removeEventListener('mouseup', this._boundDragEnd);
            const d = this.dragging;
            const endMinutes = d.currentMinutes;
            const endDate = endMinutes >= 1440 ? this.addDay(this.selectedDate) : this.selectedDate;

            window.dispatchEvent(new CustomEvent('open-create-modal', {
                detail: {
                    startDate: this.selectedDate,
                    startTime: this.minuteToTime(d.startMinutes),
                    endDate,
                    endTime: this.minuteToTime(endMinutes),
                    prefillPositionId: d.pos.position_id ? String(d.pos.position_id) : '',
                    prefillCallsign: d.pos.callsign || '',
                },
            }));
            this.dragging = null;
        },

        handleTimelineClick(event, pos) {
            if (!this.isAuthenticated) return;
            const bar = event.currentTarget;
            const pct = this.getPosFromEvent(event, bar);
            const startMinutes = this.pctToMinutes(pct);
            const endMinutes = startMinutes + 60;
            const endDate = endMinutes >= 1440 ? this.addDay(this.selectedDate) : this.selectedDate;

            window.dispatchEvent(new CustomEvent('open-create-modal', {
                detail: {
                    startDate: this.selectedDate,
                    startTime: this.minuteToTime(startMinutes),
                    endDate,
                    endTime: this.minuteToTime(endMinutes),
                    prefillPositionId: pos.position_id ? String(pos.position_id) : '',
                    prefillCallsign: pos.callsign || '',
                },
            }));
        },

        openDetailModal(pos, booking) {
            window.dispatchEvent(new CustomEvent('open-detail-modal', {
                detail: {
                    booking: {
                        id: booking.id,
                        source: booking.source,
                        ctsBookingId: booking.cts_booking_id,
                        type: booking.type,
                        position: pos.callsign,
                        date: this.selectedDate,
                        from: booking.from,
                        to: booking.to,
                        member: booking.member,
                    },
                },
            }));
        },
    }));
});
