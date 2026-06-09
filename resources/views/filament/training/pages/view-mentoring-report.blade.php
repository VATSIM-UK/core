<x-filament-panels::page>
	<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
		<div class="{{ $this->otherSessions->isNotEmpty() ? 'lg:col-span-4' : 'lg:col-span-5' }}">
			{{ $this->infolist }}
		</div>

		@if ($this->otherSessions->isNotEmpty())
			<div class="lg:col-span-1">
				{{ $this->previousSessionsInfolist }}
			</div>
		@endif
	</div>

	{{ $this->reportInfolist }}
</x-filament-panels::page>
