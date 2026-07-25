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
    startDate: "",
    startTime: "",
    endDate: "",
    endTime: "",
    selectedPosition: "",
    positionSearch: "",
    errorMessage: null,
    submitting: false,
    get startDatetime() { return this.startDate && this.startTime ? this.startDate + "T" + this.startTime : ""; },
    get endDatetime() { return this.endDate && this.endTime ? this.endDate + "T" + this.endTime : ""; },
    get duration() {
        if (!this.startDatetime || !this.endDatetime) return "";
        const s = new Date(this.startDatetime), e = new Date(this.endDatetime);
        if (isNaN(s) || isNaN(e) || e <= s) return "";
        const m = (e - s) / 60000;
        return m >= 60 ? Math.floor(m / 60) + "h " + (m % 60 ? (m % 60) + "m" : "") : m + "m";
    },
    get filteredPositions() {
        const q = this.positionSearch.toUpperCase();
        if (q.length < 2) return [];
        const data = window.qualifiedPositionsData || {};
        return Object.entries(data).filter(([id, callsign]) =>
            callsign.toUpperCase().startsWith(q)
        );
    },
    timeOptions: @json($timeSlots),
    startTimeOpen: false,
    endTimeOpen: false,
    selectTime(which, value) {
        this[which] = value;
        this[which + "Open"] = false;
    },
    filterTimes(which) {
        const q = (this[which] || "").replace(/[^0-9:]/g, "");
        if (q.length > 5) return;
        this[which] = q;
        return this.timeOptions.filter(t => t.startsWith(q));
    },
    handleTimeKeydown(which, e) {
        if (e.key === "Enter") {
            e.preventDefault();
            const matches = this.filterTimes(which);
            if (matches.length === 1) this.selectTime(which, matches[0]);
        }
    },
    getTimesForInput(which) {
        const q = (this[which] || "").replace(/[^0-9:]/g, "");
        let options = this.timeOptions.filter(t => t.startsWith(q));
        if (which === "endTime" && this.startDatetime) {
            const minDate = this.startDate;
            const minTime = this.startTime ? this.timeMinutes(this.startTime) : 0;
            options = options.filter(t => {
                if (this.endDate > this.startDate) return true;
                if (this.endDate < this.startDate) return false;
                return this.timeMinutes(t) > minTime;
            });
        }
        return options;
    },
    timeMinutes(t) {
        const [h, m] = t.split(":").map(Number);
        return h * 60 + m;
    },
}'
		x-show="open" x-cloak
		x-on:open-create-modal.window="
            const d = $event.detail || {};
            startDate = d.startDate || ''; startTime = d.startTime || '';
            endDate = d.endDate || ''; endTime = d.endTime || '';
            selectedPosition = d.prefillPositionId || '';
            positionSearch = d.prefillCallsign || '';
            errorMessage = null; submitting = false;
            open = true;
            $nextTick(() => $refs.searchInput?.focus());
        "
		x-on:close-modal.window="open = false" x-on:booking-created.window="open = false"
		x-on:booking-warning.window="errorMessage = $event.detail?.message || 'There is a scheduling conflict.'; submitting = false;"
		x-on:booking-error.window="errorMessage = $event.detail?.message || 'An error occurred'; submitting = false;"
		class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
		<div class="absolute inset-0 bg-black/50" x-on:click="open = false"></div>
		<div
			class="relative bg-white rounded-xl shadow-xl ring-1 ring-black/5 overflow-hidden w-full max-w-md mx-2 sm:mx-4 max-h-[90vh] overflow-y-auto"
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
                            errorMessage = 'Please enter a start date and time.';
                            submitting = false; return;
                        }
                        if (!endDatetime) {
                            errorMessage = 'Please enter an end date and time.';
                            submitting = false; return;
                        }
                        if (new Date(startDatetime) < new Date()) {
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
						<div class="space-y-5">
							<div>
								<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Time</label>
								<div class="grid grid-cols-2 gap-3">
									<div class="relative">
										<div class="bg-gray-50 rounded-lg px-3 py-2.5 ring-1 ring-gray-200 cursor-text"
											@click="startTimeOpen = !startTimeOpen; endTimeOpen = false; $refs.startTimeInput.focus()">
											<div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Start</div>
											<input type="date" x-model="startDate" required
												class="w-full border-0 bg-transparent text-sm p-0 focus:ring-0 mt-px">
											<input type="text" x-model="startTime" x-ref="startTimeInput" required placeholder="HH:MM"
												@focus="startTimeOpen = true; endTimeOpen = false" @input="startTimeOpen = true"
												@keydown="handleTimeKeydown('startTime', $event)"
												class="w-full border-0 bg-transparent text-sm font-medium p-0 focus:ring-0 mt-0.5">
										</div>
										<div x-show="startTimeOpen" @click.outside="startTimeOpen = false"
											class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto">
											<template x-for="t in getTimesForInput('startTime')" :key="t">
												<button type="button" x-on:click="selectTime('startTime', t)"
													class="w-full text-left px-3 py-1.5 text-sm hover:bg-gray-50 text-gray-700">
													<span x-text="t"></span>
												</button>
											</template>
										</div>
									</div>
									<div class="relative">
										<div class="bg-gray-50 rounded-lg px-3 py-2.5 ring-1 ring-gray-200 cursor-text"
											@click="endTimeOpen = !endTimeOpen; startTimeOpen = false; $refs.endTimeInput.focus()">
											<div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">End</div>
											<input type="date" x-model="endDate" required
												class="w-full border-0 bg-transparent text-sm p-0 focus:ring-0 mt-px">
											<input type="text" x-model="endTime" x-ref="endTimeInput" required placeholder="HH:MM"
												@focus="endTimeOpen = true; startTimeOpen = false" @input="endTimeOpen = true"
												@keydown="handleTimeKeydown('endTime', $event)"
												class="w-full border-0 bg-transparent text-sm font-medium p-0 focus:ring-0 mt-0.5">
										</div>
										<div x-show="endTimeOpen" @click.outside="endTimeOpen = false"
											class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto">
											<template x-for="t in getTimesForInput('endTime')" :key="t">
												<button type="button" x-on:click="selectTime('endTime', t)"
													class="w-full text-left px-3 py-1.5 text-sm hover:bg-gray-50 text-gray-700">
													<span x-text="t"></span>
												</button>
											</template>
										</div>
									</div>
								</div>
								<div x-show="duration" class="mt-2 text-xs text-gray-400 text-center">
									<i class="fa fa-clock-o mr-1" aria-hidden="true"></i>
									<span x-text="duration"></span>
								</div>
							</div>

							<div>
								<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Position</label>
								<input type="text" x-model="positionSearch" x-ref="searchInput" placeholder="Search by callsign…"
									class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand focus:ring-brand text-sm">
								<div x-show="selectedPosition" class="mt-2 flex items-center gap-2">
									<span
										class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-brand/10 text-brand text-sm font-medium rounded-full">
										<span x-text="filteredPositions.find(p => p[0] === selectedPosition)?.[1] || 'Selected'"></span>
										<button type="button" x-on:click="selectedPosition = ''"
											class="text-brand/60 hover:text-brand text-xs">&times;</button>
									</span>
								</div>
								<div x-show="positionSearch.length >= 2 && !selectedPosition" x-transition
									class="mt-2 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-48 overflow-y-auto">
									<template x-for="[id, callsign] in filteredPositions" :key="id">
										<button type="button" x-on:click="selectedPosition = id"
											:class="selectedPosition === id ? 'bg-brand/5 text-brand font-medium' : 'text-gray-700 hover:bg-gray-50'"
											class="w-full text-left px-3 py-2 text-sm block transition-colors">
											<span x-text="callsign"></span>
										</button>
									</template>
									<div x-show="filteredPositions.length === 0" class="px-3 py-4 text-sm text-gray-400 text-center">No matching
										positions</div>
								</div>
							</div>

							<div x-show="errorMessage" x-transition class="bg-red-50 border border-red-200 rounded-lg p-3">
								<p class="text-sm text-red-700" x-text="errorMessage"></p>
							</div>

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
