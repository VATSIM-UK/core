<div class="timeline-scroll">
	<div class="grid grid-cols-7 min-w-[980px] h-[calc(100vh-260px)] min-h-[320px]">
		@php $weekStart = $this->weekWindowStart(); @endphp
		@for ($i = 0; $i < 7; $i++)
			@php
				$day = $weekStart->copy()->addDays($i);
				$blocks = $this->buildWeekDayBlocks($day->toDateString());
			@endphp
			<div class="flex flex-col border-r border-gray-200 last:border-r-0 min-w-0">
				<div class="shrink-0 px-2 py-2 text-center border-b border-gray-200 bg-gray-100">
					<p class="text-xs font-semibold text-gray-800 uppercase tracking-wide m-0">
						{{ $day->format('D') }}
					</p>
					<p class="text-[11px] {{ $day->isToday() ? 'text-brand font-semibold' : 'text-gray-500' }} m-0">
						{{ $day->isToday() ? 'Today' : $day->format('d M') }}
					</p>
				</div>

				<div class="week-column-scroll flex-1 overflow-y-auto px-1.5 py-1.5 space-y-1.5">
					@forelse ($blocks as $block)
						@php $legend = $typeLegend[$block['type']] ?? null; @endphp
						<button type="button"
							@if ($block['count'] === 1) wire:click="viewBookingFromWeek('{{ $day->toDateString() }}', '{{ $block['source'] }}', {{ $block['id'] !== null ? (int) $block['id'] : 'null' }}, {{ $block['cts_booking_id'] !== null ? (int) $block['cts_booking_id'] : 'null' }})"
							@else
								wire:click="viewDayFromWeek('{{ $day->toDateString() }}')" @endif
							class="w-full text-left rounded-md border border-gray-200 hover:border-brand/60 hover:bg-brand/5 transition-colors px-2 py-1.5">
							<span class="flex items-center gap-1.5">
								<span class="w-3 h-3 rounded shrink-0 {{ $legend['colour'] ?? 'bg-gray-400' }}"></span>
								<span
									class="text-[11px] font-mono tabular-nums text-gray-500 whitespace-nowrap">{{ $block['from'] }}-{{ $block['to'] }}</span>
							</span>
							<span class="block text-[12px] font-semibold text-gray-700 truncate mt-0.5">
								{{ $block['label'] }}
							</span>
							@if ($block['count'] > 1)
								<span class="block text-[11px] text-gray-400 truncate">
									{{ $block['count'] }} bookings
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
