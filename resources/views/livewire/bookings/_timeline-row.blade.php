<div class="flex border-b border-gray-100 hover:bg-blue-50/40 transition-colors group">
	{{-- Position label --}}
	{{-- -mb-px stretches this cell over the row's own bottom border. That 1px strip
		belongs to the row box, which paints below the positioned overlays, so without
		it the gap shading and time marker show through the column at every row
		boundary. The matching border-b keeps the rule looking unchanged. --}}
	<div
		class="w-28 sm:w-40 shrink-0 px-3 py-2.5 -mb-px border-r border-r-gray-200 border-b border-b-gray-100 flex items-center gap-2 bg-gray-50 sticky left-0 z-20">
		<span class="text-sm font-bold text-gray-600 truncate" :title="pos.callsign" x-text="pos.callsign"></span>
	</div>

	{{-- Timeline bar --}}
	<div class="flex-1 relative cursor-crosshair" data-timeline-bar :style="'height: ' + rowHeight(pos)"
		@mousedown.prevent="handleTimelineMouseDown($event, pos)">
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
			<div class="absolute top-1 bottom-1 rounded border z-[8] pointer-events-none"
				:class="dragOverlaps(pos) ? 'bg-red-500/30 border-red-500/70' : 'bg-brand/30 border-brand/50'"
				:style="'left: ' + minToPct(dragging.startMinutes) + '%; width: ' + minWidth(dragging.startMinutes, dragging
				    .currentMinutes) + '%'">
				<span
					class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 whitespace-nowrap rounded px-1.5 py-0.5 text-[10px] font-mono tabular-nums text-white shadow"
					:class="dragOverlaps(pos) ? 'bg-red-600' : 'bg-uknavy'"
					x-text="minuteToTime(dragging.startMinutes) + ' \u2192 ' + minuteToTime(dragging.currentMinutes) + (
					    dragOverlaps(pos) ? ' (overlap)' : '')"></span>
			</div>
		</template>

		{{-- Booking blocks --}}
		<template x-for="booking in pos.bookings" :key="booking.source + '-' + (booking.id || booking.cts_booking_id)">
			<div
				class="absolute px-2 flex items-center gap-1.5 cursor-pointer
                text-white text-xs font-medium shadow-sm
                hover:brightness-110 hover:shadow-md transition-all z-[5] overflow-hidden whitespace-nowrap"
				data-booking-block :data-booking-key="booking.source + '-' + (booking.id || booking.cts_booking_id)"
				:class="{
				    [$bookingEdges(booking)]: true,
				    'bg-uknavy': booking.type === 'BK',
				        'bg-purple-700': booking.type === 'ME',
				        'bg-amber-800': booking.type === 'EX',
				        'bg-red-600': booking.type === 'EV',
				        'bg-orange-500': booking.type === 'GS',
				        'ring-2 ring-yellow-300 ring-inset font-bold z-[6]': isOwnBooking(booking),
				        'opacity-80 z-[9]': isResizing(booking),
				        'ring-red-500!': resizeProblem(pos, booking),
				}"
				:style="blockSpan(booking) + '; top: ' + bookingTop(pos, booking) + '; height: ' + blockHeight(pos)"
				:title="$bookingTypeLabel(booking.type) + ' \u00b7 ' +
				    (booking.member?.display_name || 'Unknown') +
				    (booking.member?.cid ? ' (' + booking.member.cid + ')' : '') +
				    (isOwnBooking(booking) ? ' \u00b7 your booking' : '') +
				    ' \u00b7 ' + booking.time_from + ' \u2013 ' + booking.time_to"
				@click.stop="openDetailModal(pos, booking)">
				<template x-if="booking.can_resize_start">
					<div class="absolute inset-y-0 left-0 w-2 cursor-ew-resize hover:bg-white/40 z-[1]"
						@mousedown.stop.prevent="startResize($event, booking, 'start')"></div>
				</template>
				<template x-if="booking.can_resize_end">
					<div class="absolute inset-y-0 right-0 w-2 cursor-ew-resize hover:bg-white/40 z-[1]"
						@mousedown.stop.prevent="startResize($event, booking, 'end')"></div>
				</template>
				{{-- Shape cue, not colour alone. First and shrink-0, so a narrow block clips
					the label rather than this. --}}
				<template x-if="booking.continues_before">
					<span class="shrink-0 -ml-1 flex items-center">
						@svg('heroicon-m-chevron-double-left', 'w-3 h-3')
						<span class="sr-only">Continues from the previous day</span>
					</span>
				</template>
				<template x-if="booking.type === 'ME'">
					@svg('heroicon-m-academic-cap', 'w-3.5 h-3.5 shrink-0')
				</template>
				<template x-if="booking.type === 'EX'">
					@svg('heroicon-m-clipboard-document-check', 'w-3.5 h-3.5 shrink-0')
				</template>
				<template x-if="booking.type === 'GS'">
					@svg('heroicon-m-user-group', 'w-3.5 h-3.5 shrink-0')
				</template>
				{{-- The icons are aria-hidden, so the type reaches assistive tech here. --}}
				<span class="sr-only" x-text="$bookingTypeLabel(booking.type)"></span>
				<span class="shrink-0 text-white/70 font-mono tabular-nums text-[11px]"
					x-text="isResizing(booking) ? resizeLabel(pos, booking) : booking.time_from"></span>
				<span class="truncate"
					x-text="(booking.member?.display_name || 'Unknown') + (booking.member?.cid ? ' (' + booking.member.cid + ')' : '')"></span>
				<template x-if="booking.continues_after">
					<span class="shrink-0 ml-auto -mr-1 flex items-center">
						@svg('heroicon-m-chevron-double-right', 'w-3 h-3')
						<span class="sr-only">Continues into the next day</span>
					</span>
				</template>
			</div>
		</template>
	</div>
</div>
