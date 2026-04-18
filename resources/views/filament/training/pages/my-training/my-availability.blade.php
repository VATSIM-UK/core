<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

        {{-- Left: Add availability form --}}
        <x-filament::section>
            <x-slot name="heading">Add Availability</x-slot>
            <x-slot name="description">
                All times are UTC (Zulu). Set an End Date to block-add the same slot across multiple days.
            </x-slot>

            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button
                    wire:click="create"
                    wire:loading.attr="disabled"
                    size="lg"
                    class="w-full"
                    icon="heroicon-m-plus"
                >
                    <span wire:loading.remove wire:target="create">Add Availability</span>
                    <span wire:loading wire:target="create">Saving…</span>
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Right: Existing slots table --}}
        <x-filament::section>
            <x-slot name="heading">My Availability</x-slot>

            {{ $this->table }}
        </x-filament::section>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>