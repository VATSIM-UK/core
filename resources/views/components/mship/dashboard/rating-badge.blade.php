@props(['code'])

@php
	$color = match ($code) {
	    'OBS' => 'bg-gray-400 text-gray-800',
	    'S1' => 'bg-green-200 text-gray-800',
	    'S2' => 'bg-blue-200 text-gray-800',
	    'S3' => 'bg-indigo-200 text-gray-800',
	    'C1' => 'bg-purple-200 text-gray-800',
	    'C3' => 'bg-amber-200 text-gray-800',
	    'P0' => 'bg-gray-200 text-gray-800',
	    'PPL' => 'bg-green-200 text-green-800',
	    'IR' => 'bg-blue-200 text-blue-800',
	    'CMEL' => 'bg-indigo-200 text-indigo-800',
	    'ATPL' => 'bg-purple-200 text-purple-800',
	    'FI' => 'bg-amber-200 text-amber-800',
	    'FE' => 'bg-pink-200 text-pink-800',
	    default => 'bg-gray-200 text-gray-800',
	};
@endphp

<span
	{{ $attributes->class(["inline-flex shrink-0 items-center rounded px-2 py-0.5 text-xs font-semibold {$color}"]) }}>
	{{ $code }}
</span>
