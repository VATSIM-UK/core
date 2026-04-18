<x-filament-panels::page>
    <style>
        .availability-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (min-width: 1024px) {
            .availability-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .availability-col {
            min-width: 0;
        }
    </style>

    <div class="availability-grid">
        <div class="availability-col">
            <x-filament::section>
                <x-slot name="heading">Add Availability</x-slot>
                <x-slot name="description">
                    All times are in Zulu.
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
        </div>
        <div class="availability-col">
            <x-filament::section>
                <x-slot name="heading">My Availability</x-slot>

                {{ $this->table }}
            </x-filament::section>
        </div>
    </div>
    <x-filament-actions::modals />
</x-filament-panels::page>