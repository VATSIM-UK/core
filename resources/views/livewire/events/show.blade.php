@php
	$minutes = (int) $event->start->diffInMinutes($event->end);
	$durationParts = array_filter([
	    intdiv($minutes, 60) ? intdiv($minutes, 60) . 'h' : null,
	    $minutes % 60 ? $minutes % 60 . 'm' : null,
	]);
	$duration = $durationParts ? implode(' ', $durationParts) : '—';
	$bookingUrl = route('site.bookings.calendar', ['year' => $event->start->year, 'month' => $event->start->month]);

	$calendarItem =
	    'no-underline flex items-center gap-2 px-4 py-2 text-base text-gray-700 hover:bg-gray-50 hover:no-underline';
	$actionButton = 'flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-base font-bold';
	$actionPrimary = 'border border-transparent bg-brand text-white hover:bg-blue-600';
	$actionPlain = 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50';
@endphp

<div class="ev-page mx-auto max-w-6xl px-4 py-6 text-base leading-normal text-gray-900 sm:px-6 lg:px-8">
	{{-- No overflow-hidden: it would clip the "Add to calendar" dropdown in the sidebar. --}}
	<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200/80">
		<div class="relative h-[200px] overflow-hidden rounded-t-xl sm:h-[260px]">
			<div class="absolute inset-0 bg-uknavy"></div>
			@if ($event->image_url)
				<img src="{{ $event->image_url }}" alt="" class="absolute inset-0 h-full w-full object-cover"
					onerror="this.remove()">
			@endif
		</div>

		<div class="flex flex-wrap items-center gap-3 bg-uknavy px-4 py-3 text-white sm:px-6">
			<a href="{{ route('site.events.index') }}" wire:navigate
				class="no-underline inline-flex items-center gap-1 text-sm font-semibold text-blue-100 hover:text-white hover:no-underline">&larr;
				All events</a>
			<p role="heading" aria-level="1" class="m-0 text-2xl font-bold tracking-tight sm:text-[23px]">{{ $event->name }}</p>
			<span
				class="ml-auto shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $event->rostered ? 'border border-amber-200 bg-amber-50 text-amber-700' : 'border border-emerald-200 bg-emerald-50 text-emerald-700' }}">
				{{ $event->rostered ? '★ Rostered' : 'Bookable' }}
			</span>
		</div>

		<div
			class="flex flex-wrap items-center gap-x-5 gap-y-2 border-b border-gray-200 bg-white px-4 py-3 text-base sm:px-6">
			@if ($event->tagline)
				<span class="min-w-[240px] flex-1 text-gray-500">{{ $event->tagline }}</span>
			@endif
			<span class="inline-flex items-center gap-1.5 font-semibold text-gray-700">
				@svg('heroicon-m-calendar-days', 'size-4 text-blue-700') {{ $event->start->toPanelDate() }}
			</span>
			<span class="inline-flex items-center gap-1.5 font-semibold text-gray-700">
				@svg('heroicon-m-clock', 'size-4 text-blue-700') {{ $event->start->toPanelTime() }} &ndash;
				{{ $event->end->toPanelTime() }}z &middot; {{ $duration }}
			</span>
		</div>

		<div class="grid grid-cols-1 gap-6 rounded-b-xl bg-gray-50 px-4 py-4 sm:p-6 lg:grid-cols-3">
			<div class="space-y-5 lg:col-span-2">
				<div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200/80">
					<p role="heading" aria-level="2" class="m-0 bg-uknavy px-4 py-2.5 text-lg font-semibold leading-snug text-white">
						About this event</p>
					<div
						class="ev-prose space-y-3 px-4 py-4 text-base leading-relaxed text-gray-700 [&_a]:text-brand [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-gray-200 [&_blockquote]:pl-4 [&_blockquote]:italic [&_h1]:text-xl [&_h1]:font-bold [&_h2]:text-lg [&_h2]:font-bold [&_h3]:font-semibold [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-3 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:pl-5">
						{!! $event->description !!}
					</div>
				</div>

				@if ($event->positions->isNotEmpty())
					<div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200/80">
						<p role="heading" aria-level="2"
							class="m-0 flex items-center gap-1.5 bg-uknavy px-4 py-2.5 text-lg font-semibold leading-snug text-white [&_svg]:text-white">
							@svg('heroicon-m-map-pin', 'size-4 shrink-0')
							Positions covered
						</p>
						<x-events.positions :positions="$event->positions" class="px-4 py-4" />
					</div>
				@endif
			</div>

			<div>
				{{-- No overflow-hidden: the dropdown below has to escape this card. --}}
				<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200/80">
					<p role="heading" aria-level="2"
						class="m-0 rounded-t-xl bg-uknavy px-4 py-2.5 text-lg font-semibold leading-snug text-white">
						Event details</p>

					<dl class="m-0 divide-y divide-gray-100 px-4 text-base">
						<div class="flex justify-between gap-4 py-2.5">
							<dt class="font-normal text-gray-500">Date</dt>
							<dd class="m-0 font-semibold text-gray-900">{{ $event->start->toPanelDate() }}</dd>
						</div>
						<div class="flex justify-between gap-4 py-2.5">
							<dt class="font-normal text-gray-500">Time</dt>
							<dd class="m-0 font-semibold text-gray-900">{{ $event->start->toPanelTime() }} &ndash;
								{{ $event->end->toPanelTime() }}z</dd>
						</div>
						<div class="flex justify-between gap-4 py-2.5">
							<dt class="font-normal text-gray-500">Duration</dt>
							<dd class="m-0 font-semibold text-gray-900">{{ $duration }}</dd>
						</div>
						<div class="flex justify-between gap-4 py-2.5">
							<dt class="font-normal text-gray-500">Positions</dt>
							<dd class="m-0 font-semibold text-gray-900">{{ $event->positions->count() }} covered</dd>
						</div>
						<div class="flex justify-between gap-4 py-2.5">
							<dt class="font-normal text-gray-500">Booking</dt>
							<dd class="m-0 font-semibold text-gray-900">{{ $event->rostered ? 'Rostered' : 'Open' }}</dd>
						</div>
						@if ($event->organiserLabels())
							<div class="flex justify-between gap-4 py-2.5">
								<dt class="font-normal text-gray-500">Organisers</dt>
								<dd class="m-0 text-right font-semibold text-gray-900">
									@foreach ($event->organiserLabels() as $label)
										<span class="block">{{ $label }}</span>
									@endforeach
								</dd>
							</div>
						@endif
					</dl>

					<div class="space-y-2 rounded-b-xl border-t border-gray-200 bg-gray-50/80 px-4 py-4">
						@unless ($event->rostered)
							<a href="{{ $bookingUrl }}"
								class="no-underline flex items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-base font-bold text-white hover:bg-blue-600 hover:no-underline">
								Book a position &rarr;
							</a>
							<p class="mb-0 text-center text-sm text-gray-500">Opens the bookings calendar for this date</p>
						@endunless

						<div x-data="{ open: false }" class="relative">
							<button type="button" @click="open = !open"
								class="{{ $actionButton }} {{ $event->rostered ? $actionPrimary : $actionPlain }}">
								@svg('heroicon-m-calendar-days', 'size-4')
								Add to calendar
							</button>
							<div x-show="open" x-cloak @click.outside="open = false"
								class="absolute z-10 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
								<a href="{{ $googleUrl }}" target="_blank" rel="noopener" class="{{ $calendarItem }}">
									@svg('heroicon-m-calendar', 'size-4 shrink-0 text-gray-400') Google Calendar
								</a>
								<a href="{{ $yahooUrl }}" target="_blank" rel="noopener" class="{{ $calendarItem }}">
									@svg('heroicon-m-calendar', 'size-4 shrink-0 text-gray-400') Yahoo Calendar
								</a>
								<a href="{{ $outlookUrl }}" target="_blank" rel="noopener" class="{{ $calendarItem }}">
									@svg('heroicon-m-calendar', 'size-4 shrink-0 text-gray-400') Outlook Web
								</a>
								<a href="{{ $officeUrl }}" target="_blank" rel="noopener" class="{{ $calendarItem }}">
									@svg('heroicon-m-calendar', 'size-4 shrink-0 text-gray-400') Outlook Desktop
								</a>
								<button type="button" wire:click="downloadIcs" class="{{ $calendarItem }} w-full text-left">
									@svg('heroicon-m-arrow-down-tray', 'size-4 shrink-0 text-gray-400') Apple/Outlook (ICS)
								</button>
							</div>
						</div>

						<button type="button" x-data="{ copied: false }"
							@click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2000)"
							class="{{ $actionButton }} {{ $actionPlain }}">
							@svg('heroicon-m-link', 'size-4')
							<span x-text="copied ? 'Link copied' : 'Copy share link'">Copy share link</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
