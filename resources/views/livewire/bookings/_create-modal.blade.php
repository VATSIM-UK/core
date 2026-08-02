<template x-teleport="body">
	@php
		$timeSlots = [];
		for ($h = 0; $h < 24; $h++) {
		    foreach ([0, 15, 30, 45] as $m) {
		        $timeSlots[] = sprintf('%02d:%02d', $h, $m);
		    }
		}
	@endphp
	<div
		x-data='{
    open: false,
    date: "",
    startTime: "",
    endTime: "",
    selectedPosition: "",
    selectedCallsign: "",
    positionSearch: "",
    errorMessage: null,
    submitting: false,
    get startDatetime() {
        if (!this.date || !this.startTime) return "";
        const ed = this.endDate;
        return (ed || this.date) + "T" + this.startTime;
    },
    get endDatetime() {
        if (!this.date || !this.endTime) return "";
        let d = this.date;
        if (this.endTime <= this.startTime) {
            d = this.addDay(this.date);
        }
        return d + "T" + this.endTime;
    },
    get endDate() {
        if (!this.date || !this.startTime || !this.endTime) return "";
        if (this.endTime > this.startTime) return "";
        return this.addDay(this.date);
    },
    get duration() {
        if (!this.startDatetime || !this.endDatetime) return "";
        const s = new Date(this.startDatetime), e = new Date(this.endDatetime);
        if (isNaN(s) || isNaN(e) || e <= s) return "";
        const m = (e - s) / 60000;
        return m >= 60 ? Math.floor(m / 60) + "h " + (m % 60 ? (m % 60) + "m" : "") : m + "m";
    },
    get filteredPositions() {
        const q = (this.positionSearch || "").toUpperCase();
        if (q.length < 2) return [];
        const data = window.qualifiedPositionsData || {};
        return Object.entries(data).filter(([id, callsign]) =>
            callsign.toUpperCase().includes(q)
        );
    },
    addDay(d) {
        const dt = new Date(d + "T00:00:00");
        dt.setDate(dt.getDate() + 1);
        return dt.toISOString().split("T")[0];
    },
    timeSlots: @json($timeSlots),
    timeMinutes(t) {
        const [h, m] = t.split(":").map(Number);
        return h * 60 + m;
    },
    validEndTimes() {
        if (!this.startTime) return this.timeSlots;
        const min = this.timeMinutes(this.startTime);
        return this.timeSlots;
    },
    selectPosition(id, callsign) {
        this.selectedPosition = id;
        this.selectedCallsign = callsign;
        this.positionSearch = "";
    },
}'
		x-show="open" x-cloak
		x-on:open-create-modal.window="
            const d = $event.detail || {};
            date = d.startDate || ''; startTime = d.startTime || ''; endTime = d.endTime || '';
            selectedPosition = d.prefillPositionId || '';
            selectedCallsign = d.prefillCallsign || '';
            positionSearch = '';
            errorMessage = null; submitting = false;
            open = true;
        "
		x-on:close-modal.window="open = false" x-on:booking-created.window="open = false"
		x-on:keydown.escape.window="open = false"
		x-on:booking-warning.window="errorMessage = $event.detail?.message || 'There is a scheduling conflict.'; submitting = false;"
		x-on:booking-error.window="errorMessage = $event.detail?.message || 'An error occurred'; submitting = false;"
		class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
		<div class="absolute inset-0 bg-black/50" x-on:click="open = false"></div>
		<div
			class="relative bg-white rounded-xl shadow-xl ring-1 ring-black/5 overflow-hidden w-full max-w-sm mx-2 sm:mx-4 max-h-[90vh] overflow-y-auto"
			@click.outside="open = false">
			<div class="bg-uknavy text-white px-5 py-3 flex items-center justify-between">
				<div class="flex items-center gap-2">
					<i class="fa fa-calendar-check shrink-0 text-sm" aria-hidden="true"></i>
					<span class="text-base font-semibold">New Booking</span>
				</div>
				<button type="button" x-on:click="open = false"
					class="text-white/60 hover:text-white text-lg leading-none">&times;</button>
			</div>

			<div class="px-5 py-5">
				@auth
					<form
						x-on:submit.prevent="
                        submitting = true;
                        errorMessage = null;

                        if (!startDatetime) {
                            errorMessage = 'Please enter a start time.';
                            submitting = false; return;
                        }
                        if (!endDatetime) {
                            errorMessage = 'Please enter an end time.';
                            submitting = false; return;
                        }
                        if (new Date(startDatetime + 'Z') < new Date()) {
                            errorMessage = 'Bookings cannot start in the past.';
                            submitting = false; return;
                        }
                        if (startDatetime === endDatetime) {
                            errorMessage = 'Booking length cannot be zero minutes.';
                            submitting = false; return;
                        }
                        if (new Date(endDatetime) <= new Date(startDatetime)) {
                            errorMessage = 'End time must be after start time.';
                            submitting = false; return;
                        }
                        if (!selectedPosition) {
                            errorMessage = 'Please select a position.';
                            submitting = false; return;
                        }

                        $wire.createBooking({ starts_at: startDatetime, ends_at: endDatetime, position_id: selectedPosition });
                    ">
						{{-- Date --}}
						<div class="mb-5">
							<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Date</label>
							<input type="date" x-model="date" required
								class="block w-full rounded-lg border-gray-300 bg-white shadow-sm focus:border-brand focus:ring-brand text-sm px-3 py-2.5">
						</div>

						{{-- Time pickers --}}
						<div class="mb-5">
							<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Time</label>
							<div class="grid grid-cols-2 gap-3">
								<div>
									<div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide mb-1.5">Start</div>
									<div class="relative">
										<select x-model="startTime"
											class="block w-full rounded-lg border-gray-300 bg-white shadow-sm focus:border-brand focus:ring-brand text-sm px-3 py-2.5 appearance-none pr-8">
											@foreach ($timeSlots as $slot)
												<option value="{{ $slot }}">{{ $slot }}</option>
											@endforeach
										</select>
										<i
											class="fa fa-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 pointer-events-none"
											aria-hidden="true"></i>
									</div>
								</div>
								<div>
									<div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide mb-1.5">End</div>
									<div class="relative">
										<select x-model="endTime"
											class="block w-full rounded-lg border-gray-300 bg-white shadow-sm focus:border-brand focus:ring-brand text-sm px-3 py-2.5 appearance-none pr-8">
											@foreach ($timeSlots as $slot)
												<option value="{{ $slot }}">{{ $slot }}</option>
											@endforeach
										</select>
										<i
											class="fa fa-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 pointer-events-none"
											aria-hidden="true"></i>
									</div>
								</div>
							</div>
							<template x-if="duration">
								<div class="mt-2 text-xs text-gray-500 text-center">
									<span x-text="duration"></span>
									<template x-if="endDate">
										<span> &middot; next day</span>
									</template>
								</div>
							</template>
						</div>

						{{-- Position --}}
						<div class="mb-5">
							<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Position</label>
							<div x-show="!selectedPosition">
								<input type="text" x-model="positionSearch" placeholder="Type callsign to search…"
									class="block w-full rounded-lg border-gray-300 bg-white shadow-sm focus:border-brand focus:ring-brand text-sm px-3 py-2.5">
								<div x-show="positionSearch.length >= 2"
									class="mt-1.5 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-44 overflow-y-auto shadow-sm">
									<template x-for="[id, callsign] in filteredPositions" :key="id">
										<button type="button" x-on:click="selectPosition(id, callsign)"
											class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
											<i class="fa fa-headphones text-[10px] text-gray-300 shrink-0" aria-hidden="true"></i>
											<span x-text="callsign"></span>
										</button>
									</template>
									<div x-show="filteredPositions.length === 0" class="px-3 py-4 text-sm text-gray-400 text-center">
										No matching positions
									</div>
								</div>
							</div>
							<div x-show="selectedPosition" class="flex items-center gap-2">
								<div class="flex-1 flex items-center gap-2 px-3 py-2.5 bg-brand/5 border border-brand/20 rounded-lg">
									<i class="fa fa-headphones text-xs text-brand shrink-0" aria-hidden="true"></i>
									<span class="text-sm font-medium text-gray-700" x-text="selectedCallsign"></span>
								</div>
								<button type="button" x-on:click="selectedPosition = ''; selectedCallsign = ''"
									class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
									&times;
								</button>
							</div>
						</div>

						{{-- Error --}}
						<div x-show="errorMessage"
							class="mb-5 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-center leading-tight">
							<span class="text-sm text-red-700" x-text="errorMessage"></span>
						</div>

						{{-- Actions --}}
						<div class="flex gap-3 pt-1">
							<button type="submit"
								class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand/90 disabled:opacity-50 transition-colors"
								:disabled="submitting || !selectedPosition || !startDatetime || !endDatetime">
								<span x-show="!submitting">Create Booking</span>
								<span x-show="submitting" x-cloak>Creating…</span>
							</button>
							<button type="button" x-on:click="open = false"
								class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
								:disabled="submitting">Cancel</button>
						</div>
					</form>
				@else
					<p class="text-gray-600 text-center py-6">Please <a href="{{ route('login') }}"
							class="text-brand hover:underline font-medium">log in</a> to book a position.</p>
				@endauth

				@guest
					<div class="flex justify-end">
						<button type="button" x-on:click="open = false"
							class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Close</button>
					</div>
				@endguest
			</div>
		</div>
	</div>
</template>
