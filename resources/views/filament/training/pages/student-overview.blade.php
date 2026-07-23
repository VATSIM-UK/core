<x-filament-panels::page>
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
		<div class="space-y-4">
			@php
				$progress = $this->getTrainingProgressData();
			@endphp

			<x-filament::section>
				<x-slot name="heading">Current Progress</x-slot>
				<div class="flex items-center gap-4 mb-1">
					<span class="font-medium">Overall Progress</span>
					<span class="ml-auto text-2xl font-bold tabular-nums">{{ $progress['percentage'] }}%</span>
				</div>
				<div class="overflow-hidden rounded-full bg-gray-100 dark:bg-gray-600">
					<div class="h-4 rounded-full transition-all duration-500"
						style="width: {{ $progress['percentage'] }}%; background-color: {{ \App\Filament\Training\Pages\StudentOverview\ViewStudentOverview::getProgressColor($progress['percentage']) }};">
					</div>
				</div>

				@if (count($progress['categories']))
					<div class="space-y-3 pt-4">
						@foreach ($progress['categories'] as $category)
							<x-filament::section collapsible compact :heading="$category['name']">
								<div class="flex items-center gap-4 mb-2">
									<div class="flex-1">
										<div class="overflow-hidden rounded-full bg-gray-100 dark:bg-gray-600 h-2">
											<div class="h-2 rounded-full transition-all duration-500"
												style="width: {{ $category['percentage'] }}%; background-color: {{ \App\Filament\Training\Pages\StudentOverview\ViewStudentOverview::getProgressColor($category['percentage']) }};">
											</div>
										</div>
									</div>
									<span class="text-sm font-medium tabular-nums">{{ $category['percentage'] }}%</span>
								</div>
								<div class="space-y-1">
									@foreach ($category['fields'] as $criterion)
										<div class="flex items-center justify-between py-0.5">
											<span class="text-sm text-gray-400 dark:text-gray-400">{{ $criterion['name'] }}</span>
											@if ($criterion['best_score'])
												<x-filament::badge :color="$criterion['best_score_color']">
													{{ $criterion['best_score_label'] }}
												</x-filament::badge>
											@else
												<x-filament::badge color="gray">Not Assessed</x-filament::badge>
											@endif
										</div>
									@endforeach
								</div>
							</x-filament::section>
						@endforeach
					</div>
				@endif
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Controlling During Training</x-slot>
				@livewire(\App\Filament\Training\Pages\StudentOverview\Widgets\TrainingPlaceControllingStatsWidget::class, ['trainingPlace' => $this->trainingPlace], key('controlling-activity-stats'))

				<x-filament::section collapsible collapsed :heading="'Time Controlling By Callsign'" class="mt-4">
					@livewire(\App\Livewire\Training\ControllingCallsignTable::class, ['trainingPlace' => $this->trainingPlace], key('controlling-callsign-table'))
				</x-filament::section>
			</x-filament::section>

			@livewire(\App\Livewire\Training\StudentAvailabilityTable::class, ['trainingPlace' => $this->trainingPlace], key('student-availability'))
		</div>

		<div>
			{{ $this->infolist }}
		</div>
	</div>
</x-filament-panels::page>
