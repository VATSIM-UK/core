<div class="timeline-scroll">
	<div class="grid grid-cols-7 min-w-[980px] h-[calc(100vh-260px)] min-h-[320px]">
		@php $weekStart = $this->weekWindowStart(); @endphp
		@for ($i = 0; $i < 7; $i++)
			@php
				$day = $weekStart->copy()->addDays($i);
				$blocks = $this->buildWeekDayBlocks($day->toDateString());
				$isPast = $day->lt(\Carbon\Carbon::today());
				$dayState = $isPast ? 'past' : ($day->isToday() ? 'today' : 'future');
			@endphp
			<div class="flex flex-col border-r border-gray-200 last:border-r-0 min-w-0">
				<button type="button" wire:click="viewDayFromWeek('{{ $day->toDateString() }}')" data-day-state="{{ $dayState }}"
					title="View {{ $day->format('l, d M Y') }}"
					class="w-full shrink-0 px-2 py-2 text-center border-b transition-colors {{ $isPast ? 'bg-gray-200 hover:bg-gray-300 border-gray-300' : 'bg-gray-100 hover:bg-gray-200 border-gray-200' }}">
					<p class="text-xs font-semibold uppercase tracking-wide m-0 {{ $isPast ? 'text-gray-500' : 'text-gray-800' }}">
						{{ $day->format('D') }}
					</p>
					<p
						class="text-[11px] {{ $day->isToday() ? 'text-brand font-semibold' : ($isPast ? 'text-gray-400' : 'text-gray-500') }} m-0">
						{{ $day->isToday() ? 'Today' : $day->format('d M') }}
					</p>
				</button>

				<div class="week-column-scroll flex-1 overflow-y-auto px-1.5 py-1.5 space-y-1.5">
					@forelse ($blocks as $block)
						@php $legend = $typeLegend[$block['type']] ?? null; @endphp
						<button type="button" x-data='{ booking: @json($block['raw']) }'
							@if ($block['count'] === 1) @click="window.dispatchEvent(new CustomEvent('open-detail-modal', { detail: { booking: { id: booking.id, source: booking.source, ctsBookingId: booking.cts_booking_id, type: booking.type, position: booking.position, date: booking.date, from: booking.from, to: booking.to, member: booking.member, event_name: booking.event_name, fromWeek: true } } }))"
							@else
								wire:click="viewDayFromWeek('{{ $day->toDateString() }}')" @endif
							class="w-full text-left rounded-md border border-gray-200 hover:border-brand/60 hover:bg-brand/5 transition-colors px-2 py-1.5 {{ $block['isOwn'] ? 'ring-2 ring-yellow-300 ring-inset' : '' }}">
							<span class="flex items-center gap-1.5">
								<span
									class="rounded shrink-0 flex items-center justify-center text-white {{ $legend['icon'] ?? null ? 'w-4 h-4' : 'w-3 h-3' }} {{ $legend['colour'] ?? 'bg-gray-400' }}">
									@if ($legend['icon'] ?? null)
										@svg($legend['icon'], 'w-3 h-3')
									@endif
								</span>
								<span
									class="text-[11px] font-mono tabular-nums text-gray-500 whitespace-nowrap">{{ $block['from'] }}-{{ $block['to'] }}</span>
							</span>
							<span class="flex items-center gap-1 mt-0.5 min-w-0">
								<span class="text-[12px] font-semibold text-gray-700 truncate">
									{{ $block['label'] }}
								</span>
								@if ($block['count'] > 1)
									<span class="text-[11px] text-gray-400 shrink-0">({{ $block['count'] }})</span>
								@endif
							</span>
							@if ($block['count'] > 1 && !empty($block['positionCodes']))
								<span class="flex items-center gap-1 mt-1">
									@foreach ($block['positionCodes'] as $code)
										@php $badge = \App\Livewire\Bookings\Calendar::POSITION_TYPE_BADGES[$code]; @endphp
										<span
											class="w-4 h-4 rounded-sm flex items-center justify-center text-[9px] font-bold text-white {{ $badge['colour'] }}"
											title="{{ $code }}">{{ $badge['letter'] }}</span>
									@endforeach
								</span>
							@endif
						</button>
					@empty
						<p class="text-[11px] text-gray-400 text-center mt-4">No bookings</p>
					@endforelse
				</div>
			</div>
		@endfor
	</div>
</div>
