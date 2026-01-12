<x-filament-panels::page>
    <div wire:poll.1s="autosave"></div>
    {{ $this->examDetailsInfoList }}

    {{ $this->form }}

    {{ $this->examResultForm }}
</x-filament-panels::page>
