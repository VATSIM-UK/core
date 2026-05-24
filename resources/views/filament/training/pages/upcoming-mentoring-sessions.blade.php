<x-filament-panels::page>
	<div class="space-y-6">
		{{ $this->table }}

		<livewire:training.pending-mentoring-reports-table :category="$this->category" :key="'pending-mentoring-reports-' . $this->category" />
	</div>
</x-filament-panels::page>
