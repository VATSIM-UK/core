@props(['event', 'past' => false])

@php($start = $event->start)

<a href="{{ route('site.events.show', $event) }}" wire:navigate
	class="group flex flex-col overflow-hidden rounded-xl bg-white no-underline shadow-sm ring-1 ring-gray-200/80 transition hover:-translate-y-0.5 hover:no-underline hover:shadow-md">
	<div class="relative {{ $past ? 'h-[112px]' : 'h-[150px]' }} overflow-hidden">
		<div class="absolute inset-0 {{ $past ? 'bg-gray-400' : 'bg-uknavy' }}"></div>
		@if ($event->image_url)
			<img src="{{ $event->image_url }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover"
				onerror="this.remove()">
		@endif

		<div class="absolute left-3 top-3 z-10 rounded-lg bg-uknavy/90 px-2 py-1 text-center leading-none text-white shadow">
			<span class="block text-[10px] uppercase tracking-wide text-blue-200">{{ $start->format('D') }}</span>
			<span class="block text-xl font-extrabold leading-none">{{ $start->format('j') }}</span>
			<span class="block text-[10px] uppercase tracking-wide text-blue-100">{{ $start->format('M') }}</span>
		</div>

		@if ($past)
			<span
				class="absolute bottom-3 right-3 z-10 rounded-full bg-gray-200 px-2 py-0.5 text-xs font-bold text-gray-600">Past</span>
		@elseif ($event->rostered)
			<span
				class="absolute bottom-3 right-3 z-10 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">&#9733;
				Rostered</span>
		@else
			<span
				class="absolute bottom-3 right-3 z-10 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">Bookable</span>
		@endif
	</div>

	<div class="flex flex-1 flex-col gap-1.5 px-4 py-3.5">
		<p role="heading" aria-level="3"
			class="m-0 text-lg font-semibold leading-snug tracking-tight text-gray-900 group-hover:text-brand">
			{{ $event->name }}</p>

		@if ($event->tagline)
			<p class="mb-0 line-clamp-2 flex-1 text-sm text-gray-500">{{ $event->tagline }}</p>
		@endif

		@if ($event->positions->isNotEmpty())
			<x-events.positions :positions="$event->positions" size="sm" :limit="4" class="mt-1" />
		@endif
	</div>

	<div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/80 px-4 py-2.5 text-sm">
		<span class="font-semibold text-gray-600">
			@if ($past)
				{{ $start->format('M Y') }}
			@else
				{{ $start->toPanelTime() }} &ndash; {{ $event->end->toPanelTime() }}z
			@endif
		</span>
		<span class="font-bold text-brand">View event &rarr;</span>
	</div>
</a>
