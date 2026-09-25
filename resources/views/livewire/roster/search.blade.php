<x-slot name="title">Search Roster</x-slot>

<main class="w-full max-w-[480px]">
	<x-filament::section>
		<x-slot name="heading">
			<span class="relative flex w-full items-center justify-center">
				<span class="absolute left-0">
					<x-filament::icon-button icon="heroicon-m-arrow-left" color="gray" size="sm" tag="a" label="Back"
						wire:navigate :href="route('site.roster.index')" />
				</span>
				<span>Find a controller</span>
			</span>
		</x-slot>

		<form wire:submit="search" class="space-y-4 text-left">
			<p class="text-sm text-gray-600">Enter a VATSIM CID to view roster status and endorsements.</p>

			<div class="space-y-1">
				<label for="roster-search" class="block text-sm font-semibold text-gray-900">VATSIM CID</label>
				<x-filament::input.wrapper>
					<x-filament::input id="roster-search" type="search" wire:model="searchTerm" required autocomplete="off"
						placeholder="e.g. 1234567" />
				</x-filament::input.wrapper>
			</div>

			<x-filament::button type="submit">Search roster</x-filament::button>
		</form>
	</x-filament::section>
</main>
