<x-filament-panels::page>
	<x-filament::section>
		<x-slot name="heading">My Availability</x-slot>
		<x-slot name="description">
			All times are in Zulu.
		</x-slot>

		<div class="mb-4 flex items-center gap-2">
			<x-filament::icon-button icon="heroicon-m-chevron-left" color="gray" size="sm" label="Previous week"
				wire:click="previousWeek" :disabled="$weekOffset === 0" />

			<span class="text-sm font-medium text-gray-700 dark:text-gray-200">
				{{ $this->visibleWeekRange() }}
			</span>

			<x-filament::icon-button icon="heroicon-m-chevron-right" color="gray" size="sm" label="Next week"
				wire:click="nextWeek" />
		</div>

		{{ $this->form }}

		<div class="mt-4">
			<x-filament::button wire:click="save">
				Save availability
			</x-filament::button>
		</div>
	</x-filament::section>

	@livewire(\App\Livewire\Training\MyAcceptedMentoringSessionsTable::class)
</x-filament-panels::page>
