<x-filament-panels::page>
    {{ $this->table }}

    @livewire(\App\Livewire\Training\ExamCancellationsTable::class, key('exam-cancellations-table'))
</x-filament-panels::page>
