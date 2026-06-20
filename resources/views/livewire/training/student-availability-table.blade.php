<x-filament::section>
	<x-slot name="heading">Upcoming Availability</x-slot>

	@if (!$this->hasPendingSession())
		<div class="mb-6">
			<x-filament::callout icon="heroicon-o-exclamation-triangle" color="warning">
				<x-slot name="heading">No Session Request</x-slot>

				<x-slot name="description">
					This student does not have a pending session request. They may have been forwarded for an exam.
				</x-slot>
			</x-filament::callout>
		</div>
	@endif

	{{ $this->table }}
</x-filament::section>
