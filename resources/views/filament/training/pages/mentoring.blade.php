<x-filament-panels::page>
	<div class="space-y-6">
		<x-filament::callout icon="heroicon-o-information-circle" color="info">
			<x-slot name="heading">
				Seeing fewer students than in CTS?
			</x-slot>

			<x-slot name="description">
				This is to be expected. Core applies stricter rules than CTS. A student won't appear if they have a session booked
				in the future, have been forwarded for an exam, or are on a LOA.
			</x-slot>
		</x-filament::callout>

		<livewire:training.availability-gantt />

		<livewire:training.accepted-mentoring-sessions-table />
	</div>
</x-filament-panels::page>
