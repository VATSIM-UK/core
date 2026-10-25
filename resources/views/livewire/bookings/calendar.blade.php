<div
	class="max-w-full mx-auto px-2 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4 sm:space-y-5 text-base leading-normal text-gray-900">
	<section class="relative bg-white rounded-xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
		<div wire:loading.delay wire:target="setTimezone" class="absolute inset-0 z-40">
			<div class="h-full flex justify-center items-start pt-28 bg-white/70">
				<div role="status"
					class="flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-md ring-1 ring-gray-200">
					@svg('heroicon-m-arrow-path', 'w-4 h-4 animate-spin text-brand')
					Updating times&hellip;
				</div>
			</div>
		</div>

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
                                    startTime: '{{ $now->format('H:00') }}',
                                    endDate: '{{ $selectedDate->format('Y-m-d') }}',
                                    endTime: '{{ $now->copy()->addHour()->format('H:00') }}',
                                    prefillPositionId: '', prefillCallsign: '',
                                }
                            }))"
							class="flex items-center px-2.5 py-1 text-xs font-semibold text-brand border border-brand/60 rounded-md hover:bg-brand hover:text-white transition-colors">
							<i class="fa fa-plus mr-1 text-[9px]" aria-hidden="true"></i> Book
						</button>
						<a href="{{ route('site.bookings.calendar.subscribe') }}"
							class="flex items-center px-2.5 py-1 [font:inherit]! [color:inherit]! no-underline! border border-brand/60 rounded-md hover:bg-brand transition-colors">
							<i class="fa fa-rss mr-1 text-[9px]" aria-hidden="true"></i> Subscribe to your bookings
						</a>
					@endauth
				</div>

				<div class="flex flex-wrap items-center justify-center gap-2 w-full sm:w-auto sm:justify-end sm:ml-auto">
					@if ($viewMode === 'day')
						<button type="button" wire:click="jumpToDate('{{ $selectedDate->copy()->subDay()->toDateString() }}')"
							class="flex items-center justify-center w-7 h-7 rounded-md bg-white/10 hover:bg-white/25 transition-colors text-white"
							title="Previous day">
							<i class="fa fa-chevron-left text-[10px]" aria-hidden="true"></i>
						</button>
						<span class="text-sm font-medium text-white/90 min-w-[140px] sm:min-w-[200px] text-center whitespace-nowrap">
							{{ $selectedDate->format('l, d. m. Y') }}
							@if ($selectedDate->isSameDay($today))
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
						<button type="button" wire:click="jumpToDate('{{ $today->toDateString() }}')"
							class="px-2.5 py-1 rounded-md bg-white/15 hover:bg-white/25 transition-colors text-[11px] font-medium text-white">
							Today
						</button>
					@else
						@php $weekStart = $this->weekWindowStart(); @endphp
						<button type="button" wire:click="jumpToDate('{{ $selectedDate->copy()->subDays(7)->toDateString() }}')"
							class="flex items-center justify-center w-7 h-7 rounded-md bg-white/10 hover:bg-white/25 transition-colors text-white"
							title="Previous week">
							<i class="fa fa-chevron-left text-[10px]" aria-hidden="true"></i>
						</button>
						<span class="text-sm font-medium text-white/90 min-w-[140px] sm:min-w-[200px] text-center whitespace-nowrap">
							Week {{ $weekStart->isoWeek() }}
							<span class="text-white/60 text-xs">&middot; {{ $weekStart->format('d M') }} -
								{{ $weekStart->copy()->addDays(6)->format('d M Y') }}</span>
						</span>
						<button type="button" wire:click="jumpToDate('{{ $selectedDate->copy()->addDays(7)->toDateString() }}')"
							class="flex items-center justify-center w-7 h-7 rounded-md bg-white/10 hover:bg-white/25 transition-colors text-white"
							title="Next week">
							<i class="fa fa-chevron-right text-[10px]" aria-hidden="true"></i>
						</button>
						<input type="date" value="{{ $selectedDate->format('Y-m-d') }}" wire:change="jumpToDate($event.target.value)"
							class="ml-1 w-[130px] rounded-md border-0 bg-white/10 px-2 py-1 text-xs text-white ring-1 ring-inset ring-white/15 focus:ring-2 focus:ring-white/40 focus:outline-none [color-scheme:dark]"
							title="Jump to the week containing this date">
						<button type="button" wire:click="jumpToDate('{{ $today->toDateString() }}')"
							class="px-2.5 py-1 rounded-md bg-white/15 hover:bg-white/25 transition-colors text-[11px] font-medium text-white">
							This week
						</button>
					@endif

					<div class="flex items-center gap-1 rounded-md bg-white/10 p-0.5 shrink-0">
						<button type="button" wire:click="setViewMode('day')"
							class="px-2.5 py-1 rounded text-[11px] font-medium transition-colors {{ $viewMode === 'day' ? 'bg-white/25 text-white' : 'text-white/70 hover:text-white' }}">
							Day
						</button>
						<button type="button" wire:click="setViewMode('week')"
							class="px-2.5 py-1 rounded text-[11px] font-medium transition-colors {{ $viewMode === 'week' ? 'bg-white/25 text-white' : 'text-white/70 hover:text-white' }}">
							Week
						</button>
					</div>
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
				<div class="relative flex-1 sm:flex-none sm:w-72" wire:key="timezone-{{ $timezone }}" wire:ignore
					x-data="bookingsTimezonePicker(@js(['current' => $timezone, 'chosen' => $timezoneChosen, 'zones' => $timezones]))" x-on:resize.window="open && position()" x-on:scroll.window="open && position()">
					<button type="button" x-ref="button" x-on:click="toggle()" aria-haspopup="listbox" :aria-expanded="open"
						aria-label="Timezone"
						class="w-full flex items-baseline gap-2 pl-2.5 pr-2.5 py-1.5 rounded-md border-0 bg-white/10 text-sm text-white text-left ring-1 ring-inset ring-white/15 hover:bg-white/15 focus:ring-2 focus:ring-white/40 focus:outline-none">
						<i class="fa fa-globe self-center text-white/40 text-[10px] shrink-0" aria-hidden="true"></i>
						<span class="truncate" x-text="currentEntry.name">{{ $timezone }}</span>
						<span class="shrink-0 font-mono tabular-nums text-[11px] text-white/60" x-text="currentEntry.offsetLabel"></span>
						<i x-show="!switching" class="fa fa-chevron-down self-center ml-auto text-white/50 text-[9px] shrink-0"
							aria-hidden="true"></i>
						@svg('heroicon-m-arrow-path', 'w-3.5 h-3.5 self-center ml-auto text-white/70 shrink-0 animate-spin', ['x-show' => 'switching', 'x-cloak' => true, 'aria-hidden' => 'true'])
					</button>

					<template x-teleport="body">
						<div x-show="open" x-cloak :style="panelStyle"
							x-on:click.outside="if (!$refs.button.contains($event.target)) close()"
							x-on:keydown.escape.prevent.stop="close(); $refs.button.focus()"
							class="fixed z-50 bg-white rounded-lg shadow-lg ring-1 ring-gray-200 overflow-hidden text-base leading-normal">
							<input type="text" x-ref="search" x-model="query" x-on:input="active = 0"
								x-on:keydown.arrow-down.prevent="move(1)" x-on:keydown.arrow-up.prevent="move(-1)"
								x-on:keydown.enter.prevent="choose(results[active]?.zone)" role="combobox" aria-autocomplete="list"
								aria-controls="bookings-timezone-list" :aria-expanded="open"
								:aria-activedescendant="results[active] ? 'bookings-timezone-' + active : null" aria-label="Search timezones"
								placeholder="City or offset, e.g. Prague or +2"
								class="block w-full border-0 border-b border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:ring-0 focus:border-gray-300">
							<ul id="bookings-timezone-list" x-ref="list" role="listbox" aria-label="Timezones"
								class="max-h-72 overflow-y-auto py-1 m-0 list-none">
								<template x-for="(entry, index) in results" :key="entry.zone">
									<li :id="'bookings-timezone-' + index" :data-index="index" role="option"
										:aria-selected="entry.zone === current" x-on:click="choose(entry.zone)" x-on:mousemove="active = index"
										:class="index === active ? 'bg-brand/10' : ''"
										class="flex items-baseline gap-2 px-3 py-1.5 text-sm text-gray-700 cursor-pointer">
										<span class="w-[4.75rem] shrink-0 font-mono tabular-nums text-xs text-gray-500"
											x-text="entry.offsetLabel"></span>
										<span class="truncate" x-text="entry.name"></span>
										<span x-show="entry.zone === browser" class="shrink-0 text-[10px] text-gray-400">your browser</span>
										@svg('heroicon-m-check', 'w-3.5 h-3.5 self-center ml-auto text-brand shrink-0', ['x-show' => 'entry.zone === current', 'aria-hidden' => 'true'])
									</li>
								</template>
								<li x-show="results.length === 0" class="px-3 py-4 text-sm text-gray-400 text-center">
									No matching timezones
								</li>
							</ul>
						</div>
					</template>
				</div>
			</div>
		</div>

		{{-- Timeline body --}}
		@if ($viewMode === 'day')
			<div wire:key="tl-{{ $selectedDate->format('Ymd') }}-{{ $filterVersion }}-{{ $dataVersion }}">
				@php
					$timelineConfig = [
					    'selectedDate' => $selectedDate->format('Y-m-d'),
					    'isAuthenticated' => auth()->check(),
					    'currentMemberCid' => auth()->check() ? (string) auth()->id() : null,
					    'isToday' => $selectedDate->isSameDay($today),
					    'nowMinutes' => (int) $now->format('H') * 60 + (int) $now->format('i'),
					    'scale' => $timelineScale,
					    'minAdvanceHours' => $minAdvanceHours,
					    'noticeCutoffMinutes' => $noticeCutoffMinutes,
					    'controllingCallsign' => $controllingCallsign,
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
														class="absolute px-2 flex items-center gap-1.5 cursor-pointer text-white text-xs font-medium shadow-sm hover:brightness-110 hover:shadow-md transition-all z-[5] overflow-hidden whitespace-nowrap bg-red-600"
														:class="$bookingEdges(booking)"
														:data-booking-key="booking.source + '-' + (booking.id || booking.cts_booking_id)"
														:style="'left: ' + booking.left_pct + '%; width: ' + booking.width_pct + '%; top: ' + bookingTop(pos,
														    booking) + '; height: ' + blockHeight(pos)"
														:title="(booking.event_name || 'Events') + ' \u00b7 ' + booking.time_from + ' \u2013 ' + booking.time_to"
														@click.stop="openDetailModal({ callsign: booking.event_name || 'Events' }, booking)">
														<template x-if="booking.continues_before">
															<span class="shrink-0 -ml-1 flex items-center">
																@svg('heroicon-m-chevron-double-left', 'w-3 h-3')
																<span class="sr-only">Continues from the previous day</span>
															</span>
														</template>
														<span class="shrink-0 text-white/70 font-mono tabular-nums text-[11px]"
															x-text="booking.time_from"></span>
														<span class="truncate" x-text="booking.event_name || 'Events'"></span>
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
									</div>
								@endif

								@foreach ($timelinePositions as $item)
									@if ($item['type'] === 'group')
										<div
											x-data='{ expanded: true, clusters: @json($item['clusters']), icao: @json($item['icao']), badges: @json(\App\Livewire\Bookings\Calendar::POSITION_TYPE_BADGES) }'>
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
																	<template x-for="code in cluster.positionCodes" :key="code">
																		<span
																			class="w-3.5 h-3.5 rounded-sm flex items-center justify-center text-[8px] font-bold text-white shrink-0"
																			:class="badges[code].colour" x-text="badges[code].letter"></span>
																	</template>
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
		@else
			<div wire:key="week-{{ $dataVersion }}-{{ $filterVersion }}">
				@include('livewire.bookings._week-list')
			</div>
		@endif

		{{-- Footer --}}
		<div
			class="border-t border-gray-200 px-4 py-2.5 bg-gray-50/80 flex flex-wrap justify-between items-center gap-x-5 gap-y-2">
			@if ($viewMode === 'day')
				<span class="text-xs text-gray-400">
					<i class="fa fa-mouse-pointer text-[10px] mr-1" aria-hidden="true"></i> Drag across an empty slot to book - or
					click
					for a 1-hour slot
				</span>
			@endif

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
								<button type="button" x-data
									@if ($isBookingOnSelectedDate) x-on:click="window.dispatchEvent(new CustomEvent('scroll-to-booking', { detail: { source: '{{ $upcomingBooking->source }}', id: {{ $upcomingBooking->id !== null ? (int) $upcomingBooking->id : 'null' }}, ctsBookingId: {{ $upcomingBooking->cts_booking_id !== null ? (int) $upcomingBooking->cts_booking_id : 'null' }}, instant: false } })); $wire.focusBooking('{{ $upcomingBooking->source }}', {{ $upcomingBooking->id !== null ? (int) $upcomingBooking->id : 'null' }})"
									@else
										wire:click="jumpToBooking('{{ $upcomingBooking->date }}', '{{ $upcomingBooking->source }}', {{ $upcomingBooking->id !== null ? (int) $upcomingBooking->id : 'null' }}, {{ $upcomingBooking->cts_booking_id !== null ? (int) $upcomingBooking->cts_booking_id : 'null' }})" @endif
									title="Jump to this booking"
									class="shrink-0 flex items-center justify-center w-10 h-10 text-brand border border-brand/60 rounded-md hover:bg-brand hover:text-white transition-colors">
									@svg('heroicon-m-arrow-right', 'w-3 h-3')
								</button>
							</div>
						</div>
					@endforeach
				@endif

				<div class="px-3 sm:px-4 py-2.5 text-xs text-gray-500">
					Get your bookings in Google Calendar, Outlook or Apple Calendar.
					<a href="{{ route('site.bookings.calendar.subscribe') }}"
						class="font-semibold text-brand hover:underline">Subscribe</a>
				</div>
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
							<button type="button" x-data
								@if ($isSessionOnSelectedDate) x-on:click="window.dispatchEvent(new CustomEvent('scroll-to-booking', { detail: { source: '{{ $upcomingSession->source }}', id: {{ $upcomingSession->id !== null ? (int) $upcomingSession->id : 'null' }}, ctsBookingId: {{ $upcomingSession->cts_booking_id !== null ? (int) $upcomingSession->cts_booking_id : 'null' }}, instant: false } })); $wire.focusBooking('{{ $upcomingSession->source }}', {{ $upcomingSession->id !== null ? (int) $upcomingSession->id : 'null' }})"
								@else
									wire:click="jumpToBooking('{{ $upcomingSession->date }}', '{{ $upcomingSession->source }}', {{ $upcomingSession->id !== null ? (int) $upcomingSession->id : 'null' }}, {{ $upcomingSession->cts_booking_id !== null ? (int) $upcomingSession->cts_booking_id : 'null' }})" @endif
								title="Jump to this booking"
								class="shrink-0 flex items-center justify-center w-10 h-10 text-brand border border-brand/60 rounded-md hover:bg-brand hover:text-white transition-colors">
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

	<div x-data="{ open: false, message: '', timer: null }"
		x-on:booking-resize-failed.window="message = $event.detail.message; open = true; clearTimeout(timer); timer = setTimeout(() => open = false, 6000)"
		x-show="open" x-cloak x-transition.opacity
		class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 z-50 flex justify-center">
		<div role="alert"
			class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg">
			@svg('heroicon-m-exclamation-triangle', 'w-4 h-4 shrink-0')
			<span x-text="message"></span>
			<button type="button" x-on:click="open = false; clearTimeout(timer)"
				class="ml-1 shrink-0 text-white/80 hover:text-white" aria-label="Dismiss">
				@svg('heroicon-m-x-mark', 'w-4 h-4')
			</button>
		</div>
	</div>
</div>

<script>
	window.isAuthenticated = @json(auth()->check());
</script>
