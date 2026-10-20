<x-mship.dashboard.card title="Calendar subscription" icon="fa fa-calendar">
	@if ($feedUrl === null)
		<div class="px-5 py-4 text-base text-gray-500">
			Sign in to subscribe to your bookings.
		</div>
	@else
		<div class="px-5 py-4 space-y-3 text-base text-gray-700">
			<p class="m-0">
				Add your VATSIM UK bookings to Google Calendar, Outlook or Apple
				Calendar. Your calendar subscribes to this link and updates itself; changes usually appear within a few
				hours.
			</p>

			<div x-data="{ copied: false }" class="flex flex-col sm:flex-row gap-2">
				<label class="sr-only" for="calendar-feed-url">Calendar feed URL</label>
				<input id="calendar-feed-url" type="text" readonly value="{{ $feedUrl }}"
					class="flex-1 min-w-0 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-xs font-mono text-gray-600" />
				<button type="button"
					x-on:click="navigator.clipboard.writeText(@js($feedUrl)).then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
					class="shrink-0 rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white hover:bg-sky-600 border-0 cursor-pointer">
					<span x-show="! copied">Copy link</span>
					<span x-show="copied" x-cloak>Copied</span>
				</button>
			</div>

			<div class="flex flex-wrap items-center gap-2">
				<a href="https://calendar.google.com/calendar/render?cid={{ urlencode($feedUrl) }}" target="_blank"
					rel="noopener noreferrer"
					class="rounded-md px-3 py-1.5 text-sm font-semibold text-brand ring-1 ring-inset ring-brand/40 hover:bg-brand/5 no-underline">
					Google Calendar
				</a>
				<a
					href="https://outlook.live.com/calendar/0/addfromweb?url={{ urlencode($feedUrl) }}&name={{ urlencode('VATSIM UK bookings') }}"
					target="_blank" rel="noopener noreferrer"
					class="rounded-md px-3 py-1.5 text-sm font-semibold text-brand ring-1 ring-inset ring-brand/40 hover:bg-brand/5 no-underline">
					Outlook
				</a>
				<a href="{{ $webcalUrl }}"
					class="rounded-md px-3 py-1.5 text-sm font-semibold text-brand ring-1 ring-inset ring-brand/40 hover:bg-brand/5 no-underline">
					Apple Calendar
				</a>
			</div>

			<p class="text-sm text-gray-500 m-0">
				Anyone with this link can read these bookings, so treat it like a password. Resetting it stops every
				existing subscription.
			</p>

			<div class="pt-1">
				<button type="button" wire:click="regenerate"
					wire:confirm="Reset the calendar link? Any calendar already subscribed to it will stop updating."
					class="rounded-md bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900 ring-1 ring-inset ring-amber-600/20 hover:bg-amber-100 border-0 cursor-pointer">
					Reset link
				</button>
			</div>
		</div>
	@endif
</x-mship.dashboard.card>
