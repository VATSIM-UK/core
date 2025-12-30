<x-filament-panels::page>
    {{ $this->infolist }}

    <h2 class="text-xl font-semibold">Mentoring overview</h2>

    @livewire(\App\Filament\Training\Pages\TrainingPlace\Widgets\MentoringSessionStatsWidget::class, ['trainingPlace' => $this->trainingPlace], key('mentoring-session-stats'))

    {{ $this->table }}

    @livewire(\App\Livewire\Training\TrainingPlaceSoloEndorsement::class, ['trainingPlace' => $this->trainingPlace], key('training-place-solo-endorsement'))

    @livewire(\App\Livewire\Training\RecentControllingTable::class, ['trainingPlace' => $this->trainingPlace], key('recent-controlling-table'))
</x-filament-panels::page>
