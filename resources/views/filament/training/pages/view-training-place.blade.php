<x-filament-panels::page>
	{{ $this->infolist }}

	<h2 class="text-xl font-semibold">Mentoring overview</h2>

	@livewire(\App\Filament\Training\Pages\TrainingPlace\Widgets\MentoringSessionStatsWidget::class, ['trainingPlace' => $this->trainingPlace], key('mentoring-session-stats'))

	{{ $this->table }}

	@livewire(\App\Livewire\Training\AvailabilityWarningsTable::class, ['trainingPlace' => $this->trainingPlace], key('availability-warnings-table'))

	<x-filament::section collapsible>
		<x-slot name="heading">Availability log</x-slot>
		<x-slot name="description">
			Every change the student made to their availability is listed here, newest first. A slot can appear more than once if
			it was later changed or extended.
		</x-slot>
		@livewire(\App\Livewire\Training\AvailabilityLogReview::class, ['trainingPlace' => $this->trainingPlace], key('availability-log-review'))
	</x-filament::section>

	@if (
		$this->trainingPlace->trainingPosition &&
			($this->trainingPlace->trainingPosition->should_show_solo_endorsement ?? true))
		@livewire(\App\Livewire\Training\TrainingPlaceSoloEndorsement::class, ['trainingPlace' => $this->trainingPlace], key('training-place-solo-endorsement'))
	@endif

	@if (
		$this->trainingPlace->trainingPosition &&
			($this->trainingPlace->trainingPosition->should_show_recent_controlling ?? true))
		@livewire(\App\Livewire\Training\RecentControllingTable::class, ['trainingPlace' => $this->trainingPlace], key('recent-controlling-table'))
	@endif

	@livewire(\App\Livewire\Training\LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace], key('leave-of-absences-table'))

	@if ($this->trainingPlace->hasExamCancellations())
		@livewire(\App\Livewire\Training\TrainingPlaceExamCancellationsTable::class, ['trainingPlace' => $this->trainingPlace], key('exam-cancellations-table'))
	@endif

	<x-filament::section collapsible collapsed>
		<x-slot name="heading">Availability checks</x-slot>
		@livewire(\App\Livewire\Training\AvailabilityChecksTable::class, ['trainingPlace' => $this->trainingPlace], key('availability-checks-table'))
	</x-filament::section>

</x-filament-panels::page>
