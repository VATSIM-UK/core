<x-filament-panels::page x-data="{}" x-init="$watch('$wire.slotData.date_range', (value) => {
    if (value?.start && !value?.end) {
        $wire.set('slotData.date_range', { start: value.start, end: value.start })
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

	<x-filament::tabs>
		<x-filament::tabs.item icon="heroicon-m-cursor-arrow-rays" :active="$mode === 'calendar'" wire:click="$set('mode', 'calendar')">
			Calendar (New)
		</x-filament::tabs.item>

		<x-filament::tabs.item icon="heroicon-m-pencil-square" :active="$mode === 'form'" wire:click="$set('mode', 'form')">
			Form
		</x-filament::tabs.item>
	</x-filament::tabs>

	@if ($mode === 'calendar')
		{{ $this->form }}
	@else
		<div class="availability-grid">
			<div class="availability-col">
				<x-filament::section>
					<x-slot name="heading">Add Availability</x-slot>
					<x-slot name="description">
						All times are in Zulu.
					</x-slot>
					<x-slot name="afterHeader">
						<x-filament::icon-button color="gray" icon="heroicon-o-cog-6-tooth" label="Change default times"
							tooltip="Change default times" wire:click="mountAction('defaultAvailabilityTimes')" />
					</x-slot>

					{{ $this->slotForm }}

					<div class="mt-6">
						<x-filament::button wire:click="create" wire:loading.attr="disabled" size="lg" class="w-full"
							icon="heroicon-m-plus">
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
						All slots are shown in Zulu.
					</x-slot>

					{{ $this->table }}
				</x-filament::section>
			</div>
		</div>
	@endif
	<x-filament-actions::modals />

	@livewire(\App\Livewire\Training\MyAcceptedMentoringSessionsTable::class)

</x-filament-panels::page>
