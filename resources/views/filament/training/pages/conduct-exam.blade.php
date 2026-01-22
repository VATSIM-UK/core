<x-filament-panels::page>
    <div wire:poll.5s="autosave"></div>
    {{ $this->examDetailsInfoList }}

    {{ $this->form }}

    {{ $this->examResultForm }}
</x-filament-panels::page>
