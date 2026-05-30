@php
	$pilotTone = $tone === 'pilot';
	$trainingTone = $tone === 'training';
@endphp

<span @class([
	'inline-flex flex-col rounded-md px-2.5 py-1.5 ring-1',
	'bg-gray-100 ring-gray-200/80' => !$pilotTone && !$trainingTone,
	'bg-emerald-50 ring-emerald-200/80' => $pilotTone,
	'bg-gray-50 ring-dashed ring-gray-300' => $trainingTone,
]) title="{{ $grantedAt->toDateTimeString() }}">
	<span @class([
		'text-sm font-bold',
		'text-uknavy' => !$pilotTone,
		'text-emerald-800' => $pilotTone,
	])>{{ $qualification }}</span>
	<span @class([
		'text-xs',
		'text-gray-500' => !$pilotTone,
		'text-emerald-700/80' => $pilotTone,
	])>{{ $grantedAt->diffForHumans() }}</span>
</span>
