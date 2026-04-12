<x-filament-panels::page>
    {{ $this->infolist }}

    <h2 class="text-xl font-semibold">Mentoring overview</h2>

    @livewire(\App\Filament\Training\Pages\TrainingPlace\Widgets\MentoringSessionStatsWidget::class, ['trainingPlace' => $this->trainingPlace], key('mentoring-session-stats'))

    {{ $this->table }}

    @livewire(\App\Livewire\Training\AvailabilityWarningsTable::class, ['trainingPlace' => $this->trainingPlace], key('availability-warnings-table'))

    @livewire(\App\Livewire\Training\AvailabilityChecksTable::class, ['trainingPlace' => $this->trainingPlace], key('availability-checks-table'))

    @if($this->trainingPlace->trainingPosition->should_show_solo_endorsement ?? true)
        @livewire(\App\Livewire\Training\TrainingPlaceSoloEndorsement::class, ['trainingPlace' => $this->trainingPlace], key('training-place-solo-endorsement'))
    @endif

    @if($this->trainingPlace->trainingPosition->should_show_recent_controlling ?? true)
        @livewire(\App\Livewire\Training\RecentControllingTable::class, ['trainingPlace' => $this->trainingPlace], key('recent-controlling-table'))
    @endif

    @livewire(\App\Livewire\Training\LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace], key('leave-of-absences-table'))

    @livewire(\App\Livewire\Training\TrainingPlaceExamCancellationsTable::class, ['trainingPlace' => $this->trainingPlace], key('exam-cancellations-table'))
</x-filament-panels::page>
