<x-slot name="title">Roster for {{ $account->id }}</x-slot>

<main class="w-full max-w-[480px]">
	<x-filament::section>
		<x-slot name="heading">
			<span class="relative flex w-full items-center justify-center">
				<span class="absolute left-0">
					<x-filament::icon-button icon="heroicon-m-arrow-left" color="gray" size="sm" tag="a" label="Back"
						wire:navigate :href="route('site.roster.search')" />
				</span>
				<span>Roster for {{ $account->id }}</span>
				<span class="absolute right-0">
					<x-filament::badge :color="$roster ? 'success' : 'danger'">
						{{ $roster ? 'Active' : 'Inactive' }}
					</x-filament::badge>
				</span>
			</span>
		</x-slot>

		<div class="space-y-6 text-left">
			<div>
				<div class="text-2xl font-bold">{{ $account->id }}</div>
				<div class="text-sm text-gray-500">
					{{ $account->qualification_atc }} &middot; {{ $account->primary_state?->name ?? 'Unknown' }} Member
				</div>
			</div>

			@if ($account->achievementAwards()->count() > 0)
				<div class="space-y-3">
					<div>
						<h3 class="text-sm font-semibold">Achievements</h3>
						<p class="text-xs text-gray-500">Achievements are issued to our volunteers in recognition of
							their contributions to the community.</p>
					</div>
					<div class="flex flex-wrap gap-3">
						@foreach ($account->achievementAwards()->with('achievement')->get() as $award)
							@continue(!$award->achievement)
							<div class="flex flex-col items-center gap-1 text-center" title="{{ $award->achievement?->description }}">
								@if ($award->achievement->image)
									<img src="{{ Storage::disk('public')->url($award->achievement->image) }}" alt="{{ $award->achievement->name }}"
										class="h-10 w-10 rounded-full">
								@endif
								<span class="text-sm font-medium">{{ $award->achievement->name }}</span>
								<span class="text-xs text-gray-500">{{ $award->created_at?->toFormattedDateString() }}</span>
							</div>
						@endforeach
					</div>
				</div>
			@endif

			<div class="space-y-2">
				<h3 class="text-sm font-semibold">Endorsements</h3>
				@forelse ($account->endorsements()->active()->get()->groupBy('type') as $type => $endorsements)
					<div class="space-y-1">
						<span class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $type }}</span>
						@foreach ($endorsements as $endorsement)
							<div class="flex items-start justify-between gap-3 border-b border-gray-100 py-2 last:border-0">
								<div>
									<div class="text-sm font-medium">
										{{ $endorsement->endorsable?->name ?? 'Unknown Endorsement' }}
									</div>
									<div class="text-xs text-gray-500">
										{{ $endorsement->endorsable?->description ?? 'No description available' }}
									</div>
								</div>
								@if ($endorsement->expires())
									<x-filament::badge color="gray">
										Expires {{ $endorsement->expires_at->toFormattedDateString() }}
									</x-filament::badge>
								@endif
							</div>
						@endforeach
					</div>
				@empty
					<p class="text-sm text-gray-500">No active endorsements.</p>
				@endforelse
			</div>

			@if ($roster && $roster->restrictionNote === null)
				<form wire:submit="search" class="space-y-3">
					<div>
						<label for="position-search" class="block text-sm font-semibold text-gray-900">Check a
							position</label>
						<p class="text-xs text-gray-500">Enter a callsign to check whether this controller can staff
							it.</p>
					</div>
					<div class="flex gap-2">
						<x-filament::input.wrapper class="flex-1">
							<x-filament::input id="position-search" type="text" wire:model="searchTerm" required autocomplete="off"
								placeholder="e.g. EGKK or EGKK_APP" />
						</x-filament::input.wrapper>
						<x-filament::button type="submit">Check</x-filament::button>
					</div>
				</form>
			@endif

			@if ($positions)
				@if (count($positions) === 1)
					@php($canControl = $roster?->accountCanControl($positions[0]) ?? false)
					<x-filament::callout :color="$canControl ? 'success' : 'danger'" :icon="$canControl ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'" :description="$account->id . ' ' . ($canControl ? 'can' : 'cannot') . ' control ' . $positions[0]->callsign . '.'" />
				@else
					<table class="w-full text-sm">
						<thead>
							<tr class="text-left text-xs uppercase tracking-wide text-gray-400">
								<th class="py-2">Position</th>
								<th class="py-2">Can control</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($positions as $position)
								@php($canControl = $roster?->accountCanControl($position) ?? false)
								<tr class="border-t border-gray-100">
									<td class="py-2">{{ $position->callsign }}</td>
									<td class="py-2">
										@if ($canControl)
											<span class="sr-only">Can control</span>
											<x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5 text-success-500" />
										@else
											<span class="sr-only">Cannot control</span>
											<x-filament::icon icon="heroicon-o-x-circle" class="h-5 w-5 text-danger-500" />
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				@endif
			@endif

			@if (!$roster)
				<x-filament::callout color="danger" icon="heroicon-o-x-circle" :description="$account->id . ' cannot control any UK positions.'" />
			@endif

			@if ($roster && $roster->restrictionNote)
				<x-filament::callout color="danger" icon="heroicon-o-exclamation-triangle" :description="$roster->restrictionNote->content" />
			@endif
		</div>
	</x-filament::section>
</main>
