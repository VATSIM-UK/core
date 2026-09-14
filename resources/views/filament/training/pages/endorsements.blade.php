<x-filament-panels::page>
	<x-filament::tabs>
		@foreach ($this->getTabs() as $key => $tab)
			@if ($tab['visible'])
				<x-filament::tabs.item :active="$activeTab === $key" :icon="$tab['icon']" wire:click="$set('activeTab', '{{ $key }}')"
					tag="button">
					{{ $tab['label'] }}
				</x-filament::tabs.item>
			@endif
		@endforeach
	</x-filament::tabs>

	@foreach ($this->getTabs() as $key => $tab)
		@if ($tab['visible'] && $activeTab === $key)
			<livewire:endorsements-resource-table :resource="$tab['resource']" :key="'endorsements-tab-' . $key" />
		@endif
	@endforeach
</x-filament-panels::page>
