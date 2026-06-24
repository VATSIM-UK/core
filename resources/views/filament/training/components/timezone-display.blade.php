<div x-data="{}" x-init="fetch(@json(route('training.timezone.detect')), {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': @json(csrf_token())
    },
    body: JSON.stringify({ timezone: Intl.DateTimeFormat().resolvedOptions().timeZone })
})"
	class="flex items-center gap-1.5 px-2 py-1 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap select-none cursor-default"
	title="Times are shown in {{ $timezone }}. Click your name above to change it.">
	<x-heroicon-o-globe-alt class="w-4 h-4" />
	<span class="hidden sm:inline">{{ $label }}</span>
</div>
