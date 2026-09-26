<div class="ev-page mx-auto max-w-6xl space-y-5 px-4 py-6 text-base leading-normal text-gray-900 sm:px-6 lg:px-8">
	<div>
		<p role="heading" aria-level="1" class="m-0 text-3xl font-bold tracking-tight text-gray-900">Events</p>
		<p class="mt-1 mb-0 max-w-prose text-base text-gray-500">Fly or control with us. Every published VATSIM UK
			event, with the airspace and positions it covers.</p>
	</div>

	<div id="upcoming" class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200/80">
		<div class="flex shrink-0 items-center justify-between gap-3 bg-uknavy px-4 py-2.5 text-white">
			<p role="heading" aria-level="2"
				class="m-0 flex items-center gap-1.5 text-lg font-semibold leading-snug text-white [&_svg]:text-white">
				@svg('heroicon-m-calendar-days', 'size-4 shrink-0')
				Upcoming events
			</p>
			<span class="shrink-0 rounded-full bg-white/15 px-2 py-0.5 text-xs font-semibold">{{ $upcoming->count() }}</span>
		</div>

		@if ($upcoming->isEmpty())
			<p class="mb-0 px-4 py-12 text-center text-base text-gray-500">There are no upcoming events right now. Check
				back soon.</p>
		@else
			<div class="grid grid-cols-1 gap-5 px-4 py-4 sm:grid-cols-2 lg:grid-cols-3">
				@foreach ($upcoming as $event)
					<x-events.card :event="$event" />
				@endforeach
			</div>
		@endif
	</div>

	<div id="past" class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200/80">
		<div class="flex shrink-0 items-center justify-between gap-3 bg-uknavy px-4 py-2.5 text-white">
			<p role="heading" aria-level="2"
				class="m-0 flex items-center gap-1.5 text-lg font-semibold leading-snug text-white [&_svg]:text-white">
				@svg('heroicon-m-clock', 'size-4 shrink-0')
				Past events
			</p>
			<span class="shrink-0 rounded-full bg-white/15 px-2 py-0.5 text-xs font-semibold">{{ $past->total() }}</span>
		</div>

		@if ($past->isEmpty())
			<p class="mb-0 px-4 py-12 text-center text-base text-gray-500">No past events to show.</p>
		@else
			<div class="grid grid-cols-1 gap-5 px-4 py-4 sm:grid-cols-2 lg:grid-cols-3">
				@foreach ($past as $event)
					<x-events.card :event="$event" past />
				@endforeach
			</div>
			<div class="border-t border-gray-100 px-4 py-3">{{ $past->links() }}</div>
		@endif
	</div>
</div>
