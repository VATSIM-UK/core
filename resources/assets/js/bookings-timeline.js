import Alpine from 'alpinejs';

Alpine.data('bookingsTimeline', (config) => ({
    positions: config.positions,
    selectedDate: config.selectedDate,
    isAuthenticated: config.isAuthenticated,
    nowMinutes: config.nowMinutes,
    isToday: config.isToday,
    expandedGroups: {},

    init() {
        this.positions.forEach(item => {
            if (item.type === 'group') this.expandedGroups[item.icao] = true;
        });
    },

    toggleGroup(icao) {
        this.expandedGroups[icao] = !this.expandedGroups[icao];
    },

    isGroupExpanded(icao) {
        return !!this.expandedGroups[icao];
    },

    handleTimelineClick(event, pos) {
        if (!this.isAuthenticated) return;
        const bar = event.currentTarget;
        const rect = bar.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const pct = x / rect.width;
        const totalMinutes = Math.round(pct * 24 * 60);
        const snapped = Math.round(totalMinutes / 15) * 15;
        const hour = Math.floor(snapped / 60);
        const minute = snapped % 60;

        const startTime = String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
        const endMinutes = snapped + 60;
        const endHour = Math.floor(endMinutes / 60) % 24;
        const endMin = endMinutes % 60;
        const endTime = String(endHour).padStart(2, '0') + ':' + String(endMin).padStart(2, '0');
        const endDate = endMinutes >= 1440
            ? (() => {
                const d = new Date(config.selectedDate + 'T00:00:00');
                d.setDate(d.getDate() + 1);
                return d.toISOString().split('T')[0];
            })()
            : config.selectedDate;

        window.dispatchEvent(new CustomEvent('open-create-modal', {
            detail: {
                startDate: config.selectedDate,
                startTime,
                endDate,
                endTime,
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
                    position: pos.callsign,
                    date: config.selectedDate,
                    from: booking.from,
                    to: booking.to,
                    member: booking.member,
                },
            },
        }));
    },
}));
