import collapse from '@alpinejs/collapse';

// Vertical metrics for stacking overlapping bookings within a timeline row.
// These are rem, not px, because the app sets a 14px root font size (app.scss)
// and the values have to line up with the Tailwind classes they replaced:
// LANE_INSET is top-1/bottom-1, and one lane in a 2.5rem (h-10) row leaves 2rem.
const LANE_INSET = 0.25;
const LANE_GAP = 0.125;
const SINGLE_LANE_HEIGHT = 2;
const STACKED_LANE_HEIGHT = 1.25;

// Half of the current time ball (w-2.5 at a 14px root), in pixels, so it is
// hidden as soon as it touches the position column rather than half over it.
const BALL_RADIUS = 5;

// Registers on the Alpine instance bundled with Livewire (exposed as
// window.Alpine). Using the `alpine:init` hook guarantees the plugin and
// component are registered before Livewire calls Alpine.start().
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(collapse);

    window.Alpine.data('bookingsTimeline', (config) => ({
        selectedDate: config.selectedDate,
        isAuthenticated: config.isAuthenticated,
        isToday: config.isToday,
        scale: config.scale,
        dragging: null,
        nowPct: 0,
        baseMinutes: 0,
        startedAt: 0,
        scrolledBy: 0,

        init() {
            this.baseMinutes = config.nowMinutes;
            this.startedAt = Date.now();
            this.updateNow();
            this._nowTimer = setInterval(() => {
                this.updateNow();
            }, 30000);
            this._boundScroll = () => {
                this.scrolledBy = this.$el.scrollLeft;
            };
            this.$el.addEventListener('scroll', this._boundScroll, { passive: true });
        },

        destroy() {
            clearInterval(this._nowTimer);
            this.$el.removeEventListener('scroll', this._boundScroll);
        },

        // The current time ball is drawn in the hour header so the header cannot
        // clip it, which also puts it above the sticky position column. Hide it
        // once it scrolls behind that column rather than let it sit on top of the
        // callsigns. The track starts exactly where the column ends, so the ball
        // is behind the column precisely when its offset is left of the scroll.
        nowBallHidden() {
            // Read both reactive values up front. Alpine records dependencies as
            // they are touched, so returning early below before reading them would
            // leave this effect with none and it would never re-evaluate.
            const fraction = this.nowPct / 100;
            const scrolled = this.scrolledBy;
            const track = this.$refs.headerTrack;

            // Width is 0 while x-cloak still has the timeline hidden. Fail open:
            // visible is correct at the initial scroll position.
            if (!track || !track.offsetWidth) return false;

            return fraction * track.offsetWidth < scrolled + BALL_RADIUS;
        },

        updateNow() {
            this.nowPct = this.minToPct(this.nowMinutes());
        },

        nowMinutes() {
            // The timeline is in Zulu. Anchor to the server-rendered Zulu time and
            // advance it by wall-clock elapsed time. This keeps the line accurate
            // even if the page sits open or the device clock is offset, since the
            // elapsed difference is monotonic regardless of the clock's absolute value.
            return this.baseMinutes + Math.floor((Date.now() - this.startedAt) / 60000);
        },

        minToPct(minutes) {
            const m = Math.max(0, Math.min(1440, Math.round(minutes)));
            return this.scale[m] || 0;
        },

        minWidth(fromMin, toMin) {
            const f = Math.max(0, Math.min(1440, Math.round(fromMin)));
            const t = Math.max(0, Math.min(1440, Math.round(toMin)));
            return Math.max(0.3, this.minToPct(t) - this.minToPct(f));
        },

        snapToSlot(minutes) {
            return Math.floor(minutes / 15) * 15;
        },

        // A booking whose end is not after its start runs past midnight. Only the
        // part falling inside the day being rendered can conflict with a drag.
        bookingEndMinutes(booking) {
            return booking.endMin > booking.startMin ? booking.endMin : 1440;
        },

        // Vertical layout, in rem, for rows holding overlapping bookings. A row
        // with a single lane reproduces the original h-10 row and top-1/bottom-1
        // block exactly; once bookings have to stack, lanes shrink and the row
        // grows so blocks stay legible rather than being squeezed into one row.
        laneHeight(pos) {
            return (pos.laneCount || 1) > 1 ? STACKED_LANE_HEIGHT : SINGLE_LANE_HEIGHT;
        },

        rowHeight(pos) {
            const lanes = Math.max(1, pos.laneCount || 1);
            return this.rem(LANE_INSET * 2 + lanes * this.laneHeight(pos) + (lanes - 1) * LANE_GAP);
        },

        bookingTop(pos, booking) {
            return this.rem(LANE_INSET + (booking.lane || 0) * (this.laneHeight(pos) + LANE_GAP));
        },

        blockHeight(pos) {
            return this.rem(this.laneHeight(pos));
        },

        // Trim binary-floating-point noise out of the generated style strings.
        rem(value) {
            return Math.round(value * 10000) / 10000 + 'rem';
        },

        // True while the in-progress drag covers time already booked on this row,
        // so the preview can warn before the create modal is even opened.
        dragOverlaps(pos) {
            if (!this.dragging || this.dragging.pos.callsign !== pos.callsign) return false;
            const start = this.dragging.startMinutes;
            const end = this.dragging.currentMinutes;
            return (pos.bookings || []).some(
                (booking) => booking.startMin < end && this.bookingEndMinutes(booking) > start,
            );
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
                        event_name: booking.event_name,
                    },
                },
            }));
        },
    }));
});
