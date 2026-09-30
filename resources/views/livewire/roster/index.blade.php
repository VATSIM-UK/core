<x-slot name="title">Roster</x-slot>

<main class="w-full max-w-[480px]">
	@if (session()->has('success'))
		<x-filament::callout color="success" icon="heroicon-o-check-circle" class="mb-4" :description="session('success')" />
	@endif

	@if (session()->has('error'))
		<x-filament::callout color="danger" icon="heroicon-o-x-circle" class="mb-4" :description="session('error')" />
	@endif

	<x-filament::section>
		<x-slot name="heading">
			<span class="relative flex w-full items-center justify-center">
				<span>Controller Roster</span>
				<span class="absolute right-0">
					<x-filament::badge :color="$roster ? 'success' : 'danger'">
						{{ $roster ? 'Active' : 'Inactive' }}
					</x-filament::badge>
				</span>
			</span>
		</x-slot>

		<div class="space-y-4 text-left">
			<div class="text-xl font-bold">Hello, {{ auth()->user()->name_first }}!</div>

			@if ($roster)
				<p class="text-sm text-gray-600">
					You are currently <strong>active</strong> on the VATSIM UK roster and can control any positions
					listed on your
					<a class="font-semibold text-brand" wire:navigate
						href="{{ route('site.roster.show', ['account' => auth()->user()]) }}">roster page</a>.
				</p>
			@elseif (auth()->user()->hasState('DIVISION') && auth()->user()->has_controller_rating)
				<p class="text-sm text-gray-600">
					You are currently <strong>inactive</strong> on the VATSIM UK roster, and cannot control any UK
					positions until you
					<a class="font-semibold text-brand" wire:navigate href="{{ route('site.roster.renew') }}">renew
						your currency</a>.
				</p>
			@else
				<p class="text-sm text-gray-600">
					You are currently <strong>inactive</strong> on the VATSIM UK roster, and cannot control any UK
					positions. Please
					<a class="font-semibold text-brand" href="mailto:community@vatsim.uk">contact Community</a> if you
					believe this is incorrect.
				</p>
			@endif

			<div class="flex flex-wrap gap-3">
				@if ($roster)
					<x-filament::button tag="a" wire:navigate :href="route('site.roster.show', ['account' => auth()->user()])">
						View my roster
					</x-filament::button>
				@elseif (auth()->user()->hasState('DIVISION') && auth()->user()->has_controller_rating)
					<x-filament::button tag="a" wire:navigate :href="route('site.roster.renew')">
						Renew my currency
					</x-filament::button>
				@endif

				<x-filament::button tag="a" color="gray" wire:navigate :href="route('site.roster.search')">
					Search the roster
				</x-filament::button>
			</div>
		</div>
	</x-filament::section>
</main>
