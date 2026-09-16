@props(['label', 'qualification' => null])

<div {{ $attributes->class(['min-w-0']) }}>
	<p class="m-0 text-sm text-gray-500">{{ $label }}</p>
	<div class="mt-1 flex flex-wrap items-center gap-2">
		@if ($qualification)
			<x-mship.dashboard.rating-badge :code="$qualification->code" />
			<span class="text-base font-medium text-gray-900">{{ $qualification->name_long }}</span>
		@else
			<span class="text-base text-gray-500">None</span>
		@endif
	</div>
</div>
