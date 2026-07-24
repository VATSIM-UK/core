<x-filament-widgets::widget>
	@if ($session && $startsAt && $endsAt)
		<x-filament::callout icon="heroicon-o-academic-cap" color="primary">
			<x-slot name="heading">
				<div class="flex items-center justify-between gap-4">
					<div>
						Upcoming session:
						<span class="font-bold">
							{{ $session->position ?? 'Mentoring' }}
						</span>

						@if ($remainingSessions > 0)
							<x-filament::badge color="gray" size="sm">
								+{{ $remainingSessions }}
								more {{ \Illuminate\Support\Str::plural('session', $remainingSessions) }}
							</x-filament::badge>
						@endif
					</div>

					<x-filament::button tag="a" href="{{ App\Filament\Training\Pages\MyTraining\MyMentoringHistory::getUrl() }}"
						icon="heroicon-m-clock" color="gray" size="sm">
						Review Previous Reports
					</x-filament::button>
				</div>
			</x-slot>

			<x-slot name="description">
				<time datetime="{{ $startsAt->toIso8601String() }}">
					{{ $startsAt->format('D d M') }}
				</time>

				<span class="text-gray-500 dark:text-gray-400">&bull;</span>

				<span>
					{{ $startsAt->format('H:i') }}Z – {{ $endsAt->format('H:i') }}Z
				</span>

				<span class="text-gray-500 dark:text-gray-400">&bull;</span>

				<span>
					Mentor:
					<span class="font-semibold">
						{{ $session->mentor?->name ?? 'Unknown' }}
					</span>
				</span>
			</x-slot>
		</x-filament::callout>
	@endif

	@if ($exam && $examStartsAt && $examEndsAt)
		<x-filament::callout icon="heroicon-o-clipboard-document-check" color="warning" class="mt-4">
			<x-slot name="heading">
				Upcoming exam:
				<span class="font-bold">
					{{ $exam->position_1 ?? ($exam->exam ?? 'Exam') }}
				</span>
			</x-slot>

			<x-slot name="description">
				<time datetime="{{ $examStartsAt->toIso8601String() }}">
					{{ $examStartsAt->format('D d M') }}
				</time>

				<span class="text-gray-500 dark:text-gray-400">&bull;</span>

				<span>
					{{ $examStartsAt->format('H:i') }}Z – {{ $examEndsAt->format('H:i') }}Z
				</span>

				<span class="text-gray-500 dark:text-gray-400">&bull;</span>

				<span>
					Examiner:
					<span class="font-semibold">
						{{ $exam->examiners?->primaryExaminer?->name ?? 'Unknown' }}
					</span>
				</span>
			</x-slot>
		</x-filament::callout>
	@endif
</x-filament-widgets::widget>
