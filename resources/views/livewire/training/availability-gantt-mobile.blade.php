<div
	class="overflow-x-auto overflow-y-auto max-h-[600px] relative z-0 isolate rounded-lg border border-gray-200 dark:border-white/10 custom-scrollbar">
	<div class="flex flex-col min-w-max">

		<div
			class="flex sticky top-0 bg-gray-50 dark:bg-gray-800/95 backdrop-blur-sm z-30 shadow-sm border-b border-gray-200 dark:border-white/10">
			<div
				class="w-16 flex-shrink-0 sticky left-0 z-40 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-white/10 px-2 py-4 text-xs font-semibold text-gray-900 dark:text-white text-center">
				Time
			</div>
			<div class="flex-1 flex divide-x divide-gray-200 dark:divide-white/10">
				@if ($mentorSessions->isNotEmpty())
					<div
						class="flex-1 min-w-[140px] px-3 py-2 flex flex-col justify-center text-center bg-danger-50/60 dark:bg-danger-400/5">
						<div class="font-semibold text-xs text-gray-950 dark:text-white truncate">
							My sessions
						</div>
						<div class="text-[10px] text-gray-500 dark:text-gray-400">
							You
						</div>
					</div>
				@endif
				@foreach ($students as $student)
					<div class="flex-1 min-w-[140px] px-3 py-2 flex flex-col justify-center text-center bg-inherit">
						<div class="font-semibold text-xs text-gray-950 dark:text-white truncate">
							<a
								href="{{ \App\Filament\Training\Pages\Mentor\MentoringHistory::getUrl(
								    parameters: [
								        'tableFilters' => [
								            'student' => ['value' => $student->cid],
								        ],
								        'category' => \App\Services\Training\MentorPermissionService::ALL_CATEGORIES,
								    ],
								    panel: 'training',
								) }}"
								target="_blank" rel="noopener noreferrer" class="truncate">
								{{ $student->name }}
							</a>
						</div>
						<div class="text-[10px] text-gray-500 dark:text-gray-400">
							{{ $student->cid }}
						</div>
						@php
							$isAllCategories = empty($category);
							$badgeColor = $isAllCategories
							    ? \App\Filament\Training\Support\MentoringTrainingGroupBadgeColor::forCtsCallsign($student->pending_position)
							    : 'gray';
						@endphp

						@if ($student->pending_position)
							<div class="mt-0.5 shrink-0 max-w-full overflow-visible">
								<x-filament::badge :color="$badgeColor" size="sm">
									{{ $student->pending_position }}
								</x-filament::badge>
							</div>
						@endif
					</div>
				@endforeach
			</div>
		</div>

		<div class="flex relative">

			<div
				class="w-16 flex-shrink-0 sticky left-0 z-20 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-white/10 flex flex-col">
				@foreach ($hours as $hour)
					<div
						class="h-16 border-b border-gray-200 dark:border-white/5 text-[11px] text-center text-gray-500 font-medium pt-2">
						{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}
					</div>
				@endforeach
			</div>

			@php
				$firstHour = $hours[0];
				$totalHours = count($hours);
				$totalTimelineMinutes = $totalHours * 60;
				$timelineStartMinutes = $firstHour * 60;
				$rowHeightPixel = 64;
			@endphp

			<div class="flex-1 flex divide-x divide-gray-200 dark:divide-white/5 relative z-10"
				style="height: {{ $totalHours * $rowHeightPixel }}px;">

				<div class="absolute inset-0 flex flex-col pointer-events-none">
					@foreach ($hours as $hour)
						<div class="h-16 border-b border-gray-100 dark:border-white/[0.02]"></div>
					@endforeach
				</div>

				@if ($nowLinePercent !== null)
					<div class="absolute inset-x-0 z-30 pointer-events-none" data-gantt-now-line
						style="top: calc({{ $nowLinePercent }}% - 1px); height: 2px; background-color: #ef4444;"></div>
				@endif

				@if ($mentorSessions->isNotEmpty())
					<div class="flex-1 min-w-[140px] relative bg-danger-50/40 dark:bg-danger-400/5 group transition duration-75">
						@foreach ($mentorSessions as $session)
							@php
								$start = \Carbon\Carbon::parse($session->taken_from);
								$end = \Carbon\Carbon::parse($session->taken_to);

								$startMinutesFromMidnight = $start->hour * 60 + $start->minute;
								$relativeStartMinutes = max(0, $startMinutesFromMidnight - $timelineStartMinutes);
								$durationMinutes = $start->diffInMinutes($end);

								$topPercent = ($relativeStartMinutes / $totalTimelineMinutes) * 100;
								$heightPercent = ($durationMinutes / $totalTimelineMinutes) * 100;
								$label = trim(($session->position ?? '') . ' · ' . ($session->student?->name ?? 'Student'));
							@endphp

							<div
								class="absolute left-2 right-2 z-20 flex flex-col items-center justify-center p-1 rounded-md shadow-sm border ring-1 bg-danger-500/90 border-danger-600 dark:border-danger-400 ring-danger-500/30 overflow-hidden text-[10px] text-white font-medium line-clamp-2 pointer-events-none"
								style="top: {{ $topPercent }}%; height: {{ $heightPercent }}%;"
								title="{{ $label }}: {{ $start->format('H:i') }} - {{ $end->format('H:i') }}">
								{{ $label }}
							</div>
						@endforeach
					</div>
				@endif

				@foreach ($students as $student)
					<div
						class="flex-1 min-w-[140px] relative bg-inherit group hover:bg-gray-50/50 dark:hover:bg-white/5 transition duration-75">

						@foreach ($mentorSessions as $session)
							@php
								$start = \Carbon\Carbon::parse($session->taken_from);
								$end = \Carbon\Carbon::parse($session->taken_to);

								$startMinutesFromMidnight = $start->hour * 60 + $start->minute;
								$relativeStartMinutes = max(0, $startMinutesFromMidnight - $timelineStartMinutes);
								$durationMinutes = $start->diffInMinutes($end);

								$topPercent = ($relativeStartMinutes / $totalTimelineMinutes) * 100;
								$heightPercent = ($durationMinutes / $totalTimelineMinutes) * 100;
							@endphp

							<div class="absolute inset-x-0 z-10 pointer-events-none bg-danger-500/15 dark:bg-danger-400/10"
								style="top: {{ $topPercent }}%; height: {{ $heightPercent }}%;"></div>
						@endforeach

						@foreach ($student->availabilities as $avail)
							@php
								$start = \Carbon\Carbon::parse($avail->from);
								$end = \Carbon\Carbon::parse($avail->to);

								$startMinutesFromMidnight = $start->hour * 60 + $start->minute;

								$relativeStartMinutes = max(0, $startMinutesFromMidnight - $timelineStartMinutes);
								$durationMinutes = $start->diffInMinutes($end);

								$topPercent = ($relativeStartMinutes / $totalTimelineMinutes) * 100;
								$heightPercent = ($durationMinutes / $totalTimelineMinutes) * 100;
							@endphp

							<button type="button" wire:click="mountAction('acceptSession', { availability_id: {{ $avail->id }} })"
								class="absolute left-2 right-2 z-20 flex flex-col items-center justify-center p-1 rounded-md shadow-sm opacity-90 transition-all border group/block ring-1 bg-success-500 hover:bg-success-600 border-success-600 dark:border-success-400 ring-success-500/30 overflow-hidden text-[10px] text-white font-medium line-clamp-2"
								style="top: {{ $topPercent }}%; height: {{ $heightPercent }}%;"
								aria-label="{{ $student->name }}: {{ $start->format('H:i') }} - {{ $end->format('H:i') }}"
								title="{{ $student->name }}: {{ $start->format('H:i') }} - {{ $end->format('H:i') }}">
							</button>
						@endforeach

					</div>
				@endforeach

			</div>
		</div>

	</div>
</div>
