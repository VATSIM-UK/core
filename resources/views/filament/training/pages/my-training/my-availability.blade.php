<x-filament-panels::page 
    x-data="{}" 
    x-init="
        if (! $wire.browserTimezone) {
            $wire.setBrowserTimezone(Intl.DateTimeFormat().resolvedOptions().timeZone)
        }
        $watch('$wire.data.date_range', (value) => {
            if (value?.start && !value?.end) {
                $wire.set('data.date_range', { start: value.start, end: value.start })
            }
        })">
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
                    All times are currently entered and shown in <strong>{{ $this->getTimezoneLabel($this->timezone) }}</strong>.
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
                <x-slot name="description">
                    Future slots adjusted to <strong>{{ $this->getTimezoneLabel($this->timezone) }}</strong>.
                </x-slot>

                {{ $this->table }}
            </x-filament::section>
        </div>
    </div>
    <x-filament-actions::modals />
</x-filament-panels::page>