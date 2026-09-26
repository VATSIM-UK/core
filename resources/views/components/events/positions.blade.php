@props(['positions', 'size' => 'md', 'limit' => null])

@php
	$groups = $positions->groupBy(fn($position) => strtok($position->callsign, '_') ?: $position->callsign)->sortKeys();

	if ($limit) {
	    $groups = $groups->take($limit);
	}

	$chip =
	    $size === 'sm'
	        ? 'rounded border border-blue-200 bg-blue-50 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-blue-800'
	        : 'rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 font-mono text-sm font-bold text-blue-800';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap ' . ($size === 'sm' ? 'gap-1' : 'gap-2')]) }}>
	@foreach ($groups as $prefix => $group)
		@if ($group->count() === 1)
			<span class="{{ $chip }}">{{ $group->first()->callsign }}</span>
		@else
			@php($tooltipId = 'event-position-' . \Illuminate\Support\Str::random(8))
			<span
				class="group/pos relative inline-flex rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 focus-visible:ring-offset-1"
				tabindex="0" aria-describedby="{{ $tooltipId }}">
				<span class="{{ $chip }}">{{ $prefix }}<span
						class="ml-1 font-sans text-[10px] font-medium text-blue-500">{{ $group->count() }}</span></span>
				<span role="tooltip" id="{{ $tooltipId }}"
					class="pointer-events-none absolute bottom-full left-0 z-20 mb-1.5 w-max max-w-[14rem] rounded-md bg-gray-900 px-2 py-1 font-mono text-[11px] font-semibold leading-snug text-white opacity-0 shadow-lg transition-opacity group-hover/pos:opacity-100 group-focus/pos:opacity-100">{{ $group->pluck('callsign')->implode(', ') }}</span>
			</span>
		@endif
	@endforeach
</div>
