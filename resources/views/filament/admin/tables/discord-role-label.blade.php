@props(['name', 'discordId', 'colour' => null])

<span class="inline-flex flex-col gap-0.5 leading-tight">
	<span @class([
		'inline-flex items-center gap-2',
		'font-semibold text-danger-600 dark:text-danger-400' => $colour === null,
	])>
		@if ($colour !== null)
			<span class="inline-block size-3.5 shrink-0 rounded-full border border-black/15 dark:border-white/20"
				style="background-color: {{ $colour }}"></span>
		@endif
		<span>{{ $name }}</span>
	</span>
	<span class="text-xs text-gray-500 dark:text-gray-400">{{ $discordId }}</span>
</span>
