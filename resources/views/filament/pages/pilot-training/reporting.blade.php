<x-filament-panels::page>
    <form wire:submit.prevent="submit" class="bg-brand">
        {{ $this->form }}

        <x-filament::button type="submit" class="my-2 float-right bg-brand">Submit</x-filament::button>
    </form>
</x-filament-panels::page>
