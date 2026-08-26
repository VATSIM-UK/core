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
							<i class="fa fa-plus mr-1 text-[9px]" aria-hidden="true"></i> Book
						</button>
					@endauth
				</div>

				<div class="flex flex-wrap items-center justify-center gap-2 w-full sm:w-auto sm:justify-end sm:ml-auto">
					<button type="button" wire:click="jumpToDate('{{ $selectedDate->copy()->subDay()->toDateString() }}')"
						class="flex items-center justify-center w-7 h-7 rounded-md bg-white/10 hover:bg-white/25 transition-colors text-white"
						title="Previous day">
						<i class="fa fa-chevron-left text-[10px]" aria-hidden="true"></i>
					</button>
					<span class="text-sm font-medium text-white/90 min-w-[140px] sm:min-w-[200px] text-center whitespace-nowrap">
						{{ $selectedDate->format('l, d. m. Y') }}
						@if ($selectedDate->isToday())
							<span class="text-brand/90 text-xs font-normal">· today</span>
						@endif
					</span>
					<button type="button" wire:click="jumpToDate('{{ $selectedDate->copy()->addDay()->toDateString() }}')"
						class="flex items-center justify-center w-7 h-7 rounded-md bg-white/10 hover:bg-white/25 transition-colors text-white"
						title="Next day">
						<i class="fa fa-chevron-right text-[10px]" aria-hidden="true"></i>
					</button>
					<input type="date" value="{{ $selectedDate->format('Y-m-d') }}" wire:change="jumpToDate($event.target.value)"
						class="ml-1 w-[130px] rounded-md border-0 bg-white/10 px-2 py-1 text-xs text-white ring-1 ring-inset ring-white/15 focus:ring-2 focus:ring-white/40 focus:outline-none [color-scheme:dark]"
						title="Jump to date">
					<button type="button" wire:click="jumpToDate('{{ \Carbon\Carbon::today()->toDateString() }}')"
						class="px-2.5 py-1 rounded-md bg-white/15 hover:bg-white/25 transition-colors text-[11px] font-medium text-white">
						Today
					</button>
				</div>
			</div>

			<div class="px-3 sm:px-4 pb-2.5 flex items-center gap-2">
				<div class="relative flex-1 sm:flex-none sm:w-52" wire:ignore>
					<i class="fa fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-white/40 text-[10px]"
						aria-hidden="true"></i>
					<input type="text" x-data="{ filter: '{{ $positionFilter }}' }" x-model="filter"
						x-on:input.debounce.250ms="$wire.set('positionFilter', filter)" placeholder="Search callsign..."
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
				    'currentMemberCid' => auth()->check() ? (string) auth()->id() : null,
				    'isToday' => $selectedDate->isToday(),
				    'nowMinutes' => (int) now()->format('H') * 60 + (int) now()->format('i'),
				    'scale' => $timelineScale,
				];
			@endphp

			<div class="relative">
				<div
					class="sm:hidden pointer-events-none absolute top-0 right-0 w-6 h-8 z-30 bg-gradient-to-l from-gray-50/95 to-transparent">
				</div>
				<div x-data='bookingsTimeline(@json($timelineConfig))' class="timeline-scroll" x-cloak>
					<div class="min-w-[1708px] relative">
						{{-- Hour header --}}
						<div class="flex border-b border-gray-200 bg-gray-50/80 sticky top-0 z-30">
							<div
								class="w-28 sm:w-40 shrink-0 px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-r border-gray-200 sticky left-0 z-[7] bg-gray-50">
								Position
							</div>
							<div class="flex-1 relative h-8" x-ref="headerTrack">
								@foreach ($timelineHours as $th)
									@if ($th['type'] === 'gap')
										<div class="absolute top-0 bottom-0 flex items-center justify-center bg-gray-300/70"
											style="left: {{ $th['scale_left'] }}%; width: {{ $th['scale_width'] }}%" title="{{ $th['label'] }}">
											@if ($th['show_label'])
												<span class="text-[10px] text-gray-500 font-medium">{{ $th['label'] }}</span>
											@elseif ($th['show_short_label'])
												<span class="text-[10px] text-gray-500 font-medium">{{ $th['short_label'] }}</span>
											@endif
											<span class="absolute top-0 bottom-0 w-px bg-gray-300" style="left: 0px"></span>
											<span class="absolute top-0 bottom-0 w-px bg-gray-300" style="right: 0px"></span>
										</div>
									@else
										<div class="absolute top-0 bottom-0 flex items-center text-[10px] text-gray-400 font-medium"
											style="left: {{ $th['scale_left'] }}%" title="{{ sprintf('%02d:00', $th['hour']) }}">
											@if ($th['show_label'])
												<span class="pl-1.5">{{ sprintf('%02d:00', $th['hour']) }}</span>
											@endif
											<span class="absolute top-0 bottom-0 w-px bg-gray-200" style="left: -1px"></span>
										</div>
									@endif
								@endforeach

								<template x-if="isToday && !nowBallHidden()">
									<div class="absolute top-full -translate-y-1/2 -ml-[4px] w-2.5 h-2.5 bg-red-500 rounded-full"
										:style="'left: ' + nowPct + '%'"></div>
								</template>
							</div>
						</div>

						{{-- Timeline rows --}}
						@if (empty($timelinePositions) && empty($events))
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
							@if (!empty($events))
								<div class="flex border-b border-gray-200 bg-gray-50/90">
									<div
										class="w-28 sm:w-40 shrink-0 px-3 py-2.5 -mb-px border-r border-b border-gray-200 flex items-center gap-2 sticky left-0 bg-gray-50 z-20">
										<i class="fa fa-star text-[10px] text-gray-400 shrink-0" aria-hidden="true"></i>
										<span class="text-sm font-bold text-gray-600 uppercase tracking-wide">Events</span>
									</div>
									<div class="flex-1 relative" x-data='{ pos: { laneCount: @json($eventLaneCount) } }'
										:style="'height: ' + rowHeight(pos)">
										<div x-data='{ events: @json($events) }'>
											<template x-for="booking in events" :key="booking.source + '-' + (booking.id || booking.cts_booking_id)">
												<div
													class="absolute rounded px-2 flex items-center gap-1.5 cursor-pointer text-white text-xs font-medium shadow-sm hover:brightness-110 hover:shadow-md transition-all z-[5] overflow-hidden whitespace-nowrap bg-red-600"
													:style="'left: ' + booking.left_pct + '%; width: ' + booking.width_pct + '%; top: ' + bookingTop(pos,
													    booking) + '; height: ' + blockHeight(pos)"
													:title="(booking.event_name || 'Events') + ' \u00b7 ' + booking.from + ' \u2013 ' + booking.to"
													@click.stop="openDetailModal({ callsign: booking.event_name || 'Events' }, booking)">
													<span class="shrink-0 text-white/70 font-mono tabular-nums text-[11px]" x-text="booking.from"></span>
													<span class="truncate" x-text="booking.event_name || 'Events'"></span>
												</div>
											</template>
										</div>
									</div>
								</div>
							@endif

							@foreach ($timelinePositions as $item)
								@if ($item['type'] === 'group')
									<div x-data='{ expanded: true, clusters: @json($item['clusters']), icao: @json($item['icao']) }'>
										<div
											class="flex border-b border-gray-200 bg-white cursor-pointer hover:bg-brand/5 transition-colors select-none"
											@click="expanded = !expanded">
											<div
												class="w-28 sm:w-40 shrink-0 px-3 py-2.5 -mb-px border-r border-b border-gray-200 flex items-center gap-2 sticky left-0 bg-white z-20">
												<i class="fa fa-chevron-right text-[10px] text-gray-400 shrink-0 transition-transform duration-150"
													:style="expanded ? 'transform: rotate(90deg)' : ''" aria-hidden="true"></i>
												<span class="text-[13px] font-semibold text-gray-700 uppercase tracking-wide" x-text="icao"></span>
											</div>
											<div class="flex-1 h-11 sm:h-10 flex items-center">
												<template x-if="expanded">
													<span class="flex-1 border-t border-dashed border-gray-200 mx-3"></span>
												</template>
												<template x-if="!expanded">
													<div class="flex-1 relative h-6">
														<template x-for="(cluster, idx) in clusters" :key="idx">
															<div
																class="absolute top-0 bottom-0 rounded bg-uknavy/80 border border-uknavy/30 flex items-center justify-center gap-1 px-2 overflow-hidden"
																:style="'left: ' + cluster.left_pct + '%; width: ' + cluster.width_pct + '%'"
																:title="cluster.count + ' booking' + (cluster.count !== 1 ? 's' : '') + ' \u00b7 ' + cluster.memberCount +
																    ' member' + (cluster.memberCount !== 1 ? 's' : '') + ' \u00b7 ' + cluster.from + ' \u2013 ' +
																    cluster
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
									<div class="flex border-b border-gray-300 bg-gray-100 h-4">
										<div
											class="w-28 sm:w-40 shrink-0 -mb-px border-r border-r-gray-200 border-b border-b-gray-300 bg-gray-100 sticky left-0 z-20">
										</div>
										<div class="flex-1"></div>
									</div>
								@endif
							@endforeach
						@endif

						<div class="flex absolute inset-0 z-[1] pointer-events-none">
							<div class="w-28 sm:w-40 shrink-0"></div>
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
							<div class="flex absolute inset-x-0 bottom-0 top-[calc(2rem+1px)] z-[9] pointer-events-none">
								<div class="w-28 sm:w-40 shrink-0"></div>
								<div class="flex-1 relative">
									<div class="absolute inset-y-0 w-px bg-red-500" :style="'left: ' + nowPct + '%'"></div>
								</div>
							</div>
						</template>
					</div>
				</div>
			</div>
		</div>

		{{-- Footer --}}
		<div
			class="border-t border-gray-200 px-4 py-2.5 bg-gray-50/80 flex flex-wrap justify-between items-center gap-x-5 gap-y-2">
			<span class="text-xs text-gray-400">
				<i class="fa fa-mouse-pointer text-[10px] mr-1" aria-hidden="true"></i> Drag across an empty slot to book - or
				click
				for a 1-hour slot
			</span>

			<div class="relative w-full sm:w-auto">
				<ul
					class="legend-scroll flex flex-nowrap sm:flex-wrap items-center gap-x-3 gap-y-1.5 m-0 p-0 list-none overflow-x-auto sm:overflow-visible">
					@foreach ($typeLegend as $code => $legend)
						<li class="flex items-center gap-1.5 shrink-0">
							<span class="w-4 h-4 rounded shrink-0 flex items-center justify-center text-white {{ $legend['colour'] }}">
								@if ($legend['icon'])
									@svg($legend['icon'], 'w-3 h-3')
								@endif
							</span>
							<span class="text-xs text-gray-500 whitespace-nowrap">{{ $legend['label'] }}</span>
						</li>
					@endforeach
					@auth
						<li class="flex items-center gap-1.5 shrink-0">
							<span class="w-4 h-4 rounded shrink-0 bg-uknavy ring-2 ring-yellow-300 ring-inset"></span>
							<span class="text-xs text-gray-500 whitespace-nowrap">Your booking</span>
						</li>
					@endauth
				</ul>
				<div
					class="sm:hidden pointer-events-none absolute inset-y-0 right-0 w-6 bg-gradient-to-l from-gray-50 to-transparent">
				</div>
			</div>
		</div>
	</section>

	<div class="flex flex-col lg:flex-row gap-4 lg:items-start max-w-5xl mx-auto">
		@auth
			<section class="flex-1 min-w-0 bg-white rounded-xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
				<div class="bg-uknavy text-white">
					<div class="px-3 sm:px-4 py-2.5 flex items-center gap-2">
						@svg('heroicon-m-calendar-days', 'w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0 text-white')
						<p class="text-sm sm:text-base font-semibold leading-snug text-white m-0">My future bookings</p>
					</div>
				</div>

				@if ($upcomingBookings->isEmpty())
					<div class="px-4 py-10 text-center text-gray-400">
						<p class="text-sm">No upcoming bookings.</p>
					</div>
				@else
					{{-- Column headers --}}
					<div class="flex border-b border-gray-200 bg-gray-50/80">
						<div
							class="w-24 sm:w-32 shrink-0 px-2 sm:px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-r border-gray-200">
							Date
						</div>
						<div class="flex-1 min-w-0 px-2 sm:px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
							Callsign</div>
						<div class="w-14 shrink-0 px-2 py-2"></div>
					</div>

					@foreach ($upcomingBookings as $upcomingBooking)
						@php $isBookingOnSelectedDate = \Carbon\Carbon::parse($upcomingBooking->date)->isSameDay($selectedDate); @endphp
						<div class="flex border-b border-gray-100 hover:bg-blue-50/40 transition-colors">
							<div
								class="w-24 sm:w-32 shrink-0 px-2 sm:px-3 py-2.5 border-r border-r-gray-200 flex flex-col justify-center gap-0.5">
								<span class="text-[13px] font-semibold text-gray-700 whitespace-nowrap">
									<span class="sm:hidden">{{ \Carbon\Carbon::parse($upcomingBooking->date)->format('d.m.') }}</span>
									<span class="max-sm:hidden">{{ \Carbon\Carbon::parse($upcomingBooking->date)->format('D, d. m.') }}</span>
								</span>
								<span class="text-[11px] text-gray-400 font-mono tabular-nums whitespace-nowrap">
									{{ sprintf('%s - %s', $upcomingBooking->from, $upcomingBooking->to) }}
								</span>
							</div>
							<div class="flex-1 min-w-0 px-2 sm:px-3 py-2.5 flex items-center">
								<span class="text-[13px] font-semibold text-gray-700 font-mono truncate">
									{{ $upcomingBooking->position ?? 'Unknown' }}
								</span>
							</div>
							<div class="w-14 shrink-0 px-2 py-2.5 flex items-center justify-center">
								<button type="button" @disabled($isBookingOnSelectedDate)
									@unless ($isBookingOnSelectedDate) wire:click="jumpToDate('{{ $upcomingBooking->date }}')" @endunless
									title="{{ $isBookingOnSelectedDate ? 'Already showing this date' : 'Jump to this date' }}"
									class="shrink-0 flex items-center justify-center w-10 h-10 text-brand border border-brand/60 rounded-md hover:bg-brand hover:text-white transition-colors disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-brand">
									@svg('heroicon-m-arrow-right', 'w-3 h-3')
								</button>
							</div>
						</div>
					@endforeach
				@endif
			</section>
		@endauth

		<section class="flex-1 min-w-0 bg-white rounded-xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
			<div class="bg-uknavy text-white">
				<div class="px-3 sm:px-4 py-2.5 flex items-center gap-2">
					@svg('heroicon-m-academic-cap', 'w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0 text-white')
					<p class="text-sm sm:text-base font-semibold leading-snug text-white m-0">Upcoming mentoring &amp; exam
						sessions</p>
				</div>
			</div>

			@if ($upcomingMentoringExamBookings->isEmpty())
				<div class="px-4 py-10 text-center text-gray-400">
					<p class="text-sm">No upcoming mentoring sessions or exams.</p>
				</div>
			@else
				{{-- Column headers --}}
				<div class="flex border-b border-gray-200 bg-gray-50/80">
					<div
						class="w-24 sm:w-32 shrink-0 px-2 sm:px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-r border-gray-200">
						Date
					</div>
					<div class="flex-1 min-w-0 px-2 sm:px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
						Callsign</div>
					<div class="w-24 sm:w-32 shrink-0 px-2 sm:px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
						Mentor/Examiner</div>
					<div class="w-9 shrink-0 px-1 py-2"></div>
					<div class="w-14 shrink-0 px-2 py-2"></div>
				</div>

				@foreach ($upcomingMentoringExamBookings as $upcomingSession)
					@php
						$isSessionOnSelectedDate = \Carbon\Carbon::parse($upcomingSession->date)->isSameDay($selectedDate);
						$legend = $typeLegend[$upcomingSession->type] ?? null;
					@endphp
					<div class="flex border-b border-gray-100 hover:bg-blue-50/40 transition-colors">
						<div
							class="w-24 sm:w-32 shrink-0 px-2 sm:px-3 py-2.5 border-r border-r-gray-200 flex flex-col justify-center gap-0.5">
							<span class="text-[13px] font-semibold text-gray-700 whitespace-nowrap">
								<span class="sm:hidden">{{ \Carbon\Carbon::parse($upcomingSession->date)->format('d.m.') }}</span>
								<span class="max-sm:hidden">{{ \Carbon\Carbon::parse($upcomingSession->date)->format('D, d. m.') }}</span>
							</span>
							<span class="text-[11px] text-gray-400 font-mono tabular-nums whitespace-nowrap">
								{{ sprintf('%s - %s', $upcomingSession->from, $upcomingSession->to) }}
							</span>
						</div>
						<div class="flex-1 min-w-0 px-2 sm:px-3 py-2.5 flex items-center">
							<span class="text-[13px] font-semibold text-gray-700 font-mono truncate">
								{{ $upcomingSession->position ?? 'Unknown' }}
							</span>
						</div>
						<div class="w-24 sm:w-32 shrink-0 px-2 sm:px-3 py-2.5 flex items-center">
							<span class="text-[13px] text-gray-700 truncate">
								{{ $upcomingSession->member['display_name'] }}
							</span>
						</div>
						<div class="w-9 shrink-0 flex items-center justify-center"
							title="{{ $legend['label'] ?? $upcomingSession->type }}">
							@if ($legend)
								<span class="w-6 h-6 rounded shrink-0 flex items-center justify-center text-white {{ $legend['colour'] }}">
									@if ($legend['icon'])
										@svg($legend['icon'], 'w-3.5 h-3.5')
									@endif
								</span>
							@endif
						</div>
						<div class="w-14 shrink-0 px-2 py-2.5 flex items-center justify-center">
							<button type="button" @disabled($isSessionOnSelectedDate)
								@unless ($isSessionOnSelectedDate) wire:click="jumpToDate('{{ $upcomingSession->date }}')" @endunless
								title="{{ $isSessionOnSelectedDate ? 'Already showing this date' : 'Jump to this date' }}"
								class="shrink-0 flex items-center justify-center w-10 h-10 text-brand border border-brand/60 rounded-md hover:bg-brand hover:text-white transition-colors disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-brand">
								@svg('heroicon-m-arrow-right', 'w-3 h-3')
							</button>
						</div>
					</div>
				@endforeach
			@endif
		</section>
	</div>

	@include('livewire.bookings._create-modal')
	@include('livewire.bookings._detail-modal')
</div>

<script>
	window.isAuthenticated = @json(auth()->check());
</script>
