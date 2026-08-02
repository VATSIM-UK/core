<div
	class="max-w-full mx-auto px-2 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4 sm:space-y-5 text-base leading-normal text-gray-900">
	<section class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
		{{-- Header --}}
		<div class="bg-uknavy text-white">
			<div class="px-3 sm:px-4 py-2.5 flex flex-wrap items-center gap-x-4 gap-y-1.5">
				<div class="flex items-center gap-3">
					<p
						class="flex items-center gap-1.5 text-sm sm:text-base font-semibold leading-snug text-white m-0 whitespace-nowrap">
						<i class="fa fa-calendar shrink-0 text-xs sm:text-sm text-white" aria-hidden="true"></i>
						Bookings Calendar
					</p>
					@auth
						<button type="button" x-data
							x-on:click="window.dispatchEvent(new CustomEvent('open-create-modal', {
                                detail: {
                                    startDate: '{{ $selectedDate->format('Y-m-d') }}',
                                    startTime: '{{ now()->format('H:00') }}',
                                    endDate: '{{ $selectedDate->format('Y-m-d') }}',
                                    endTime: '{{ now()->addHour()->format('H:00') }}',
                                    prefillPositionId: '', prefillCallsign: '',
                                }
                            }))"
							class="px-2.5 py-1 text-xs font-semibold text-brand border border-brand/60 rounded-md hover:bg-brand hover:text-white transition-colors">
							<i class="fa fa-plus mr-1 text-[9px]" aria-hidden="true"></i> New
						</button>
					@endauth
				</div>

				<div class="flex items-center gap-2 ml-auto">
					<a
						href="{{ route('site.bookings.calendar', ['year' => $selectedDate->copy()->subDay()->year, 'month' => $selectedDate->copy()->subDay()->month]) }}?day={{ $selectedDate->copy()->subDay()->day }}"
						class="flex items-center justify-center w-7 h-7 rounded-md bg-white/10 hover:bg-white/25 transition-colors text-white"
						title="Previous day" wire:navigate>
						<i class="fa fa-chevron-left text-[10px]" aria-hidden="true"></i>
					</a>
					<span class="text-sm font-medium text-white/90 min-w-[140px] text-center tabular-nums">
						{{ $selectedDate->format('D, j M Y') }}
						@if ($selectedDate->isToday())
							<span class="text-brand/90 text-xs font-normal">· today</span>
						@endif
					</span>
					<a
						href="{{ route('site.bookings.calendar', ['year' => $selectedDate->copy()->addDay()->year, 'month' => $selectedDate->copy()->addDay()->month]) }}?day={{ $selectedDate->copy()->addDay()->day }}"
						class="flex items-center justify-center w-7 h-7 rounded-md bg-white/10 hover:bg-white/25 transition-colors text-white"
						title="Next day" wire:navigate>
						<i class="fa fa-chevron-right text-[10px]" aria-hidden="true"></i>
					</a>
					<input type="date" value="{{ $selectedDate->format('Y-m-d') }}"
						x-on:change="
							const d = new Date($event.target.value + 'T00:00:00');
							const y = d.getFullYear();
							const m = d.getMonth() + 1;
							const day = d.getDate();
							window.Livewire.navigate('/atc/bookings/calendar/' + y + '/' + m + '?day=' + day);
						"
						class="ml-1 w-[130px] rounded-md border-0 bg-white/10 px-2 py-1 text-xs text-white ring-1 ring-inset ring-white/15 focus:ring-2 focus:ring-white/40 focus:outline-none [color-scheme:dark]"
						title="Jump to date">
					<a href="{{ route('site.bookings.calendar') }}"
						class="px-2.5 py-1 rounded-md bg-white/15 hover:bg-white/25 transition-colors text-[11px] font-medium text-white"
						wire:navigate>Today</a>
				</div>
			</div>

			<div class="px-3 sm:px-4 pb-2.5 flex items-center gap-2">
				<div class="relative flex-1 sm:flex-none sm:w-52" wire:ignore>
					<i class="fa fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-white/40 text-[10px]"
						aria-hidden="true"></i>
					<input type="text" x-data="{ filter: '{{ $positionFilter }}' }" x-model="filter"
						x-on:input.debounce.250ms="$wire.set('positionFilter', filter)" placeholder="Filter callsign…"
						class="w-full pl-7 pr-3 py-1.5 rounded-md border-0 bg-white/10 text-sm text-white placeholder-white/40 ring-1 ring-inset ring-white/15 focus:ring-2 focus:ring-white/40 focus:outline-none focus:bg-white/15 transition-all">
				</div>
			</div>
		</div>

		{{-- Timeline body --}}
		<div wire:key="tl-{{ $selectedDate->format('Ymd') }}-{{ $filterVersion }}-{{ $dataVersion }}">
			@php
				$timelineConfig = [
				    'selectedDate' => $selectedDate->format('Y-m-d'),
				    'isAuthenticated' => auth()->check(),
				    'nowMinutes' => (int) now()->format('H') * 60 + (int) now()->format('i'),
				    'isToday' => $selectedDate->isToday(),
				    'scale' => $timelineScale,
				];
			@endphp

			<div x-data='bookingsTimeline(@json($timelineConfig))' class="timeline-scroll" x-cloak>
				<div class="min-w-[1680px] relative">
					{{-- Hour header --}}
					<div class="flex border-b border-gray-200 bg-gray-50/80 sticky top-0 z-10">
						<div
							class="w-32 shrink-0 px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-r border-gray-200">
							Position
						</div>
						<div class="flex-1 relative h-8">
							@foreach ($timelineHours as $th)
								@if ($th['type'] === 'gap')
									<div class="absolute top-0 bottom-0 flex items-center justify-center bg-gray-300/70"
										style="left: {{ $th['scale_left'] }}%; width: {{ $th['scale_width'] }}%">
										<span class="text-[10px] text-gray-500 font-medium">{{ $th['label'] }}</span>
										<span class="absolute top-0 bottom-0 w-px bg-gray-300" style="left: 0px"></span>
										<span class="absolute top-0 bottom-0 w-px bg-gray-300" style="right: 0px"></span>
									</div>
								@else
									<div class="absolute top-0 bottom-0 flex items-center text-[10px] text-gray-400 font-medium"
										style="left: {{ $th['scale_left'] }}%">
										<span class="pl-1.5">{{ sprintf('%02d:00', $th['hour']) }}</span>
										<span class="absolute top-0 bottom-0 w-px bg-gray-200" style="left: -1px"></span>
									</div>
								@endif
							@endforeach
						</div>
					</div>

					{{-- Timeline rows --}}
					@if (empty($timelinePositions))
						<div class="px-4 py-16 text-center text-gray-400">
							<p class="text-sm font-medium text-gray-500">No positions available for this date.</p>
							<p class="text-xs mt-1">
								@auth
									<span>Try a different date or adjust the filter.</span>
								@else
									<span>Log in to see bookable positions.</span>
								@endauth
							</p>
						</div>
					@else
						@foreach ($timelinePositions as $item)
							@if ($item['type'] === 'group')
								<div x-data='{ expanded: true, clusters: @json($item['clusters']), icao: @json($item['icao']) }'>
									<div
										class="flex border-b border-gray-200 bg-gray-50/90 cursor-pointer hover:bg-brand/5 transition-colors select-none"
										@click="expanded = !expanded">
										<div
											class="w-32 shrink-0 px-3 py-2.5 border-r border-gray-200 flex items-center gap-2 sticky left-0 bg-gray-50/90 z-[6]">
											<i class="fa fa-chevron-right text-[10px] text-gray-400 shrink-0 transition-transform duration-150"
												:style="expanded ? 'transform: rotate(90deg)' : ''" aria-hidden="true"></i>
											<span class="text-sm font-bold text-gray-600 uppercase tracking-wide" x-text="icao"></span>
										</div>
										<div class="flex-1 h-10 flex items-center px-3">
											<template x-if="expanded">
												<span class="flex-1 border-t border-dashed border-gray-200"></span>
											</template>
											<template x-if="!expanded">
												<div class="flex-1 relative h-6">
													<template x-for="(cluster, idx) in clusters" :key="idx">
														<div
															class="absolute top-0 bottom-0 rounded bg-uknavy/80 border border-uknavy/30 flex items-center justify-center gap-1 px-2 overflow-hidden"
															:style="'left: ' + cluster.left_pct + '%; width: ' + cluster.width_pct + '%'"
															:title="cluster.count + ' booking' + (cluster.count !== 1 ? 's' : '') + ' \u00b7 ' + cluster.memberCount +
															    ' member' + (cluster.memberCount !== 1 ? 's' : '') + ' \u00b7 ' + cluster.from + ' \u2013 ' + cluster
															    .to">
															<span class="truncate text-[11px] font-medium text-white/90"
																x-text="cluster.count + ' booking' + (cluster.count !== 1 ? 's' : '')"></span>
														</div>
													</template>
												</div>
											</template>
										</div>
									</div>
									<div x-show="expanded" x-collapse>
										@foreach ($item['positions'] as $pos)
											<div x-data='{ pos: @json($pos) }'>
												@include('livewire.bookings._timeline-row')
											</div>
										@endforeach
									</div>
								</div>
							@elseif ($item['type'] === 'single')
								<div x-data='{ pos: @json($item) }'>
									@include('livewire.bookings._timeline-row')
								</div>
							@elseif ($item['type'] === 'separator')
								<div class="flex border-b border-gray-300 bg-gray-100/70 h-4"></div>
							@endif
						@endforeach
					@endif

					<div class="flex absolute inset-0 z-[1] pointer-events-none">
						<div class="w-32 shrink-0"></div>
						<div class="flex-1 relative">
							@foreach ($timelineHours as $th)
								@if ($th['type'] === 'gap')
									<div class="absolute inset-y-0 bg-gray-400/25 border-x border-gray-300/70"
										style="left: {{ $th['scale_left'] }}%; width: {{ $th['scale_width'] }}%"></div>
								@endif
							@endforeach
						</div>
					</div>

					<template x-if="isToday">
						<div class="absolute inset-y-0 w-px bg-red-500 z-30 pointer-events-none" :style="'left: ' + nowPct() + '%'">
							<div class="w-2.5 h-2.5 bg-red-500 rounded-full -ml-[4px] -mt-[4px]"></div>
						</div>
					</template>
				</div>
			</div>
		</div>

		{{-- Footer --}}
		<div class="border-t border-gray-200 px-4 py-2.5 bg-gray-50/80 flex items-center">
			<span class="text-xs text-gray-400">
				<i class="fa fa-mouse-pointer text-[10px] mr-1" aria-hidden="true"></i> Drag across an empty slot to book - or click
				for a 1-hour slot
			</span>
		</div>
	</section>

	@include('livewire.bookings._create-modal')
	@include('livewire.bookings._detail-modal')
</div>

<script>
	window.qualifiedPositionsData = @json($qualifiedPositions);
	window.isAuthenticated = @json(auth()->check());
</script>
