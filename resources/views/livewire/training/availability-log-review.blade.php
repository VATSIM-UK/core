<div>
	<div class="mb-4 flex items-end gap-2">
		<div class="flex-1">
			{{ $this->form }}
		</div>
		<x-filament::button wire:click="setAsOfToNow" icon="heroicon-m-clock">
			Now
		</x-filament::button>
	</div>
	<p class="mb-4 mt-1 text-xs text-gray-400">All times are in Zulu.</p>

	{{ $this->table }}

	<div class="mt-4">
		<x-filament::section collapsible collapsed>
			<x-slot name="heading">Log history</x-slot>
			@livewire(\App\Livewire\Training\AvailabilityLogTable::class, ['trainingPlace' => $this->trainingPlace], key('availability-log-table'))
		</x-filament::section>
	</div>
</div>
