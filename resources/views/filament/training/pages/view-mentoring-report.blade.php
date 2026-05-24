<x-filament-panels::page>
	<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
		<div class="lg:col-span-4">
			{{ $this->infolist }}
		</div>

		<div class="lg:col-span-1">
			{{ $this->previousSessionsInfolist }}
		</div>
	</div>

	{{ $this->reportInfolist }}
</x-filament-panels::page>
