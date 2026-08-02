<template x-teleport="body">
	<div x-data="{
    open: false,
    booking: null,
    confirmDelete: false,
    ended: false,
    computeEnded(b) {
        if (!b || !b.date || !b.to) return false;
        let endDate = b.date;
        if (b.from && b.to <= b.from) {
            const d = new Date(b.date + 'T00:00:00Z');
            d.setUTCDate(d.getUTCDate() + 1);
            endDate = d.toISOString().split('T')[0];
        }
        return new Date(endDate + 'T' + b.to + ':00Z') < new Date();
    },
}" x-show="open" x-cloak
		x-on:open-detail-modal.window="open = true; booking = $event.detail?.booking || null; confirmDelete = false; ended = computeEnded(booking);"
		x-on:close-modal.window="open = false" x-on:booking-deleted.window="open = false"
		x-on:keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-center justify-center"
		style="display: none;">
		<div class="absolute inset-0 bg-black/50" x-on:click="open = false"></div>
		<div
			class="relative bg-white rounded-xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden w-full max-w-sm mx-2 sm:mx-4"
			@click.outside="open = false">
			<div class="bg-uknavy text-white px-4 py-2.5 flex items-center gap-1.5">
				<i class="fa fa-info-circle shrink-0 text-sm text-white" aria-hidden="true"></i>
				<span class="text-base font-semibold leading-snug">Booking Details</span>
			</div>

			<template x-if="booking">
				<div class="px-5 py-4 space-y-3">
					<div>
						<span class="text-xs font-medium uppercase tracking-wide text-gray-500">Position</span>
						<p class="text-gray-900 font-medium" x-text="booking.position"></p>
					</div>
					<div class="grid grid-cols-2 gap-4">
						<div>
							<span class="text-xs font-medium uppercase tracking-wide text-gray-500">Date</span>
							<p class="text-gray-900" x-text="booking.date"></p>
						</div>
						<div>
							<span class="text-xs font-medium uppercase tracking-wide text-gray-500">Time</span>
							<p class="text-gray-900">
								<span x-text="booking.from"></span> &ndash; <span x-text="booking.to"></span>
							</p>
						</div>
					</div>
					<div>
						<span class="text-xs font-medium uppercase tracking-wide text-gray-500">Member</span>
						<p class="text-gray-900"
							x-text="(booking.member?.display_name || booking.member?.name) + (booking.member?.cid ? ' (' + booking.member.cid + ')' : '')">
						</p>
					</div>

					<template x-if="booking.member?.id == '{{ auth()->id() }}' && !ended && booking.type === 'BK'">
						<div class="pt-4 border-t border-gray-200">
							<template x-if="!confirmDelete">
								<button type="button" x-on:click="confirmDelete = true"
									class="w-full px-4 py-2 text-sm font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 border border-red-200">
									Delete Booking
								</button>
							</template>
							<template x-if="confirmDelete">
								<div class="space-y-3">
									<p class="text-sm text-red-700">Are you sure you want to delete this booking?</p>
									<div class="flex gap-3">
										<button type="button" x-on:click="confirmDelete = false"
											class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
										<button type="button"
											x-on:click="$wire.cancelBooking({ id: booking.id, cts_booking_id: booking.ctsBookingId })"
											class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Delete</button>
									</div>
								</div>
							</template>
						</div>
					</template>
				</div>
			</template>

			<div class="px-5 py-3 bg-gray-50 flex justify-end border-t border-gray-200">
				<button type="button" x-on:click="open = false"
					class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md hover:bg-gray-100 border border-gray-300 shadow-sm">Close</button>
			</div>
		</div>
	</div>
</template>
