<x-slot name="title">Renew Roster Currency</x-slot>

<main class="w-full max-w-[480px]">
	<x-filament::section>
		<x-slot name="heading">
			<span class="relative flex w-full items-center justify-center">
				<span class="absolute left-0">
					<x-filament::icon-button icon="heroicon-m-arrow-left" color="gray" size="sm" tag="a" label="Back"
						wire:navigate :href="route('site.roster.index')" />
				</span>
				<span>Renew roster currency</span>
			</span>
		</x-slot>

		<div class="space-y-6 text-left">
			<div class="flex items-center gap-3">
				<span
					class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold {{ $page === 1 ? 'bg-brand text-white' : 'bg-success-500 text-white' }}">
					@if ($page === 1)
						1
					@else
						<x-filament::icon icon="heroicon-m-check" class="h-4 w-4" />
					@endif
				</span>
				<span class="text-sm {{ $page === 1 ? 'font-semibold text-gray-900' : 'text-gray-400' }}">Notifications</span>
				<span class="h-px flex-1 bg-gray-200"></span>
				<span
					class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold {{ $page === 2 ? 'bg-brand text-white' : 'bg-gray-100 text-gray-400' }}">2</span>
				<span class="text-sm {{ $page === 2 ? 'font-semibold text-gray-900' : 'text-gray-400' }}">Reactivate</span>
			</div>

			@if ($page === 1)
				@if (!$canReactivate)
					@if ($lastTwoQuartersFailed)
						<x-filament::callout color="danger" icon="heroicon-o-exclamation-triangle" :description="'You have not met the minimum controlling requirements in the last two consecutive quarters and cannot automatically reactivate your roster membership.'" />
					@else
						<x-filament::callout color="danger" icon="heroicon-o-exclamation-triangle" :description="'As it has been 18 months since your last ATC session you cannot automatically reactivate your roster membership.'" />
					@endif

					<p class="text-sm text-gray-600">
						Please <a href="mailto:atc-training@vatsim.uk" class="font-semibold text-brand">contact ATC
							Training</a>.
					</p>
				@else
					<div class="space-y-2 text-sm text-gray-600">
						<p>It has been a while! Our records show that your last controlling session was {{ $lastLogon }}.</p>
						<p>Because of this, you are required to reactivate onto the VATSIM UK controlling roster.</p>
						<p>Before you do this, please take a read through what has changed in the Division and
							Procedurally whilst you've been gone!</p>
					</div>

					@if ($notifications->count() > 0)
						<div class="space-y-2 text-sm text-gray-600">
							<p>There have been a few changes since you have been gone! Please take the time to read
								through the below notifications, acknowledging as you read through them.</p>
							<p>Click on a notification to see more and mark as read.</p>
						</div>
					@endif

					<p class="text-sm font-semibold">You have {{ count($notifications) }} notifications to read.</p>

					<div class="space-y-2">
						@foreach ($notifications as $notification)
							<div wire:key="notification-{{ $notification['id'] }}" class="rounded-lg border border-gray-200"
								x-data="{ expanded: false }">
								<button type="button" class="flex w-full items-center justify-between gap-2 p-3 text-left"
									x-on:click="expanded = ! expanded" x-bind:aria-expanded="expanded ? 'true' : 'false'"
									aria-controls="notification-panel-{{ $notification['id'] }}">
									<span class="text-sm font-semibold">{{ $notification['title'] }}</span>
									<x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4 shrink-0"
										x-bind:class="expanded && 'rotate-180'" />
								</button>
								<div id="notification-panel-{{ $notification['id'] }}" x-show="expanded" x-collapse
									class="space-y-2 border-t border-gray-100 p-3">
									<p class="text-sm text-gray-600">{{ $notification['body'] }}</p>
									@if ($notification['link'])
										<a href="{{ $notification['link'] }}" target="_blank" class="text-sm font-semibold text-brand">Read more</a>
									@endif
									<button type="button" class="text-sm font-semibold text-brand"
										wire:click="markNotificationRead({{ $notification['id'] }})">Mark read</button>
								</div>
							</div>
						@endforeach
					</div>

					<x-filament::button wire:click="nextPage" :disabled="$this->reactivateButtonDisabled">
						Reactivate{{ $notifications->isNotEmpty() ? ' · ' . $notifications->count() . ' unread' : '' }}
					</x-filament::button>
				@endif
			@else
				<div class="space-y-2 text-sm text-gray-600">
					<p>Once you are added back onto the roster you must maintain a minimum of 3 hours controlling any
						UK position within a calendar quarter e.g. January -&gt; March.</p>
					<p>Click the button below to add yourself back onto the roster.</p>
				</div>

				<x-filament::button wire:click="reactivate">Add to roster</x-filament::button>
			@endif
		</div>
	</x-filament::section>
</main>
