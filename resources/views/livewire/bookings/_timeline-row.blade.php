<div class="flex border-b border-gray-100 hover:bg-blue-50/40 transition-colors group">
	{{-- Position label --}}
	<div class="w-32 shrink-0 px-3 py-2.5 border-r border-gray-200 flex items-center gap-2 bg-white sticky left-0 z-[6]">
		<span class="text-[13px] font-semibold text-gray-700 truncate" x-text="pos.callsign"></span>
	</div>

	{{-- Timeline bar --}}
	<div class="flex-1 relative h-10 cursor-crosshair" @mousedown.prevent="handleTimelineMouseDown($event, pos)">
		{{-- Empty state hint --}}
		<template x-if="pos.bookings.length === 0">
			<div
				class="absolute inset-x-4 top-1/2 -translate-y-1/2 border-t border-dashed border-gray-200 group-hover:border-brand/30 transition-colors">
			</div>
		</template>

		{{-- Hour grid lines --}}
		<template x-for="h in 23" :key="h">
			<div class="absolute top-0 bottom-0 border-l border-gray-100" :style="'left: ' + minToPct(h * 60) + '%'"></div>
		</template>

		{{-- Drag preview --}}
		<template x-if="dragging && dragging.pos.callsign === pos.callsign">
			<div class="absolute top-1 bottom-1 rounded bg-brand/30 border border-brand/50 z-[8] pointer-events-none"
				:style="'left: ' + minToPct(dragging.startMinutes) + '%; width: ' + minWidth(dragging.startMinutes, dragging
				    .currentMinutes) + '%'">
				<span
					class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 whitespace-nowrap rounded bg-uknavy px-1.5 py-0.5 text-[10px] font-mono tabular-nums text-white shadow"
					x-text="minuteToTime(dragging.startMinutes) + ' → ' + minuteToTime(dragging.currentMinutes)"></span>
			</div>
		</template>

		{{-- Booking blocks --}}
		<template x-for="booking in pos.bookings" :key="booking.source + '-' + (booking.id || booking.cts_booking_id)">
			<div
				class="absolute top-1 bottom-1 rounded px-2 flex items-center gap-1.5 cursor-pointer
                text-white text-xs font-medium shadow-sm
                hover:brightness-110 hover:shadow-md transition-all z-[5] overflow-hidden whitespace-nowrap"
				data-booking-block
				:class="{
				    'bg-uknavy': booking.type === 'BK',
				    'bg-purple-700': booking.type === 'ME',
				    'bg-amber-800': booking.type === 'EX',
				    'bg-red-600': booking.type === 'EV',
				    'bg-orange-500': booking.type === 'GS',
				}"
				:style="'left: ' + booking.left_pct + '%; width: ' + booking.width_pct + '%'"
				:title="(booking.member?.display_name || booking.member?.name || 'Unknown') + (booking.member?.cid ? ' (' + booking.member
				    .cid + ')' : '') + ' \u00b7 ' + booking.from + ' \u2013 ' + booking.to"
				@click.stop="openDetailModal(pos, booking)">
				<span class="shrink-0 text-white/70 font-mono tabular-nums text-[11px]" x-text="booking.from"></span>
				<span class="truncate"
					x-text="(booking.member?.display_name || booking.member?.name || 'Unknown') + (booking.member?.cid ? ' (' + booking.member.cid + ')' : '')"></span>
			</div>
		</template>
	</div>
</div>
