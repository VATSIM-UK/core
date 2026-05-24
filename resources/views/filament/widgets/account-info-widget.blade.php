<x-filament-widgets::widget>
	<x-filament::section>
		<div class="relative">
			{{-- Header --}}
			<div class="flex items-center justify-between gap-4">
				<h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
					Welcome back, {{ $user->name }}
				</h2>

				{{-- Active / Inactive badge --}}
				<span
					class="shrink-0 px-4 py-1.5 text-xs font-semibold rounded-full
                    {{ $rosterStatus
																				    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
																				    : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200' }}"
					title="Roster Status">
					{{ $rosterStatus ? 'Active' : 'Inactive' }}
				</span>
			</div>

			<div class="space-y-3 mt-5">

				{{-- Main grid --}}
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-8 text-sm">

					<div x-data="{
	    copied: false,
	    copy(text) {
	        navigator.clipboard.writeText(text);
	        this.copied = true;
	        setTimeout(() => { this.copied = false; }, 2000);
	    }
	}" class="relative inline-block">
						<span class="text-gray-500">CID</span>
						<div
							class="font-medium cursor-pointer hover:text-primary-600 dark:hover:text-primary-400 transition underline underline-offset-4 decoration-dotted"
							@click="copy('{{ $user->id }}')" title="Click to copy">
							{{ $user->id }}
							<div x-show="copied" x-transition
								class="absolute mt-1 text-xs p-1 rounded bg-gray-900 text-white dark:bg-gray-700">CID
								copied</div>
						</div>
					</div>

					<div>
						<span class="text-gray-500">Email</span>
						<div class="font-medium">{{ $user->email }}</div>
					</div>

					<div>
						<span class="text-gray-500">Joined</span>
						<div class="font-medium">{{ $user->created_at->diffForHumans() }}</div>
					</div>

					<div>
						<span class="text-gray-500">Membership Status</span>
						<div class="font-medium">
							{{ $user->primary_state?->name ? $user->primary_state->name : '' }}
						</div>
					</div>

				</div>

				<div class="border-t border-gray-200 dark:border-gray-700 mt-5 mb-2"></div>

				@if ($panel === 'training')
					{{-- Mentor Groups --}}
					@if ($atcMentorGroups->isNotEmpty() || $pilotMentorGroups->isNotEmpty())
						<div class="mt-3">
							<span>Mentor</span>

							<div class="mt-4 flex flex-wrap gap-2">

								{{-- ATC Mentor Groups --}}
								@foreach ($atcMentorGroups as $group)
									<span
										class="px-2.5 py-1 text-xs rounded-md bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-200 shadow-sm">
										{{ $group }}
									</span>
								@endforeach

								{{-- Pilot Mentor Groups --}}
								@foreach ($pilotMentorGroups as $group)
									<span
										class="px-2.5 py-1 text-xs rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200 shadow-sm">
										{{ $group }}
									</span>
								@endforeach

							</div>
						</div>
					@endif

					{{-- Examiner Levels --}}
					@if ($examinerGroups->isNotEmpty())
						<div class="mt-3">
							<span>Examiner</span>

							<div class="mt-4 flex flex-wrap gap-2">
								@foreach ($examinerGroups as $group)
									<span
										class="
                                    px-2.5 py-1 text-xs rounded-md shadow-sm
                                    {{ $group['type'] === 'all'
																																				    ? 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200'
																																				    : ($group['type'] === 'atc'
																																				        ? 'bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-200'
																																				        : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200') }}
                                    ">
										{{ $group['label'] }}
									</span>
								@endforeach
							</div>
						</div>
					@endif

					{{-- Endorsements --}}
					<div>
						<span>Endorsements</span>

						<div class="mt-4 flex flex-wrap gap-2">
							@if ($endorsements->isEmpty())
								<span class="text-gray-400 text-sm">None</span>
							@else
								@foreach ($endorsements as $endorsement)
									<span
										class="px-2.5 py-1 text-xs rounded-md bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200 shadow-sm">
										{{ $endorsement }}
									</span>
								@endforeach
							@endif
						</div>
					</div>

				@endif


				{{-- Roles --}}
				@if ($panel === 'app')
					<div class="mt-3">
						<span>Roles</span>

						<div class="mt-4 flex flex-wrap gap-2">
							@if ($roles->isEmpty())
								<span class="text-gray-400 text-sm">None</span>
							@else
								@foreach ($roles as $role)
									<span
										class="px-2.5 py-1 text-xs rounded-md bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200 shadow-sm">
										{{ $role }}
									</span>
								@endforeach
							@endif
						</div>
					</div>
				@endif

				<div class="border-t border-gray-200 dark:border-gray-700 mt-5 mb-3"></div>

				{{-- Ratings Row --}}
				<div class="flex flex-wrap items-center justify-center gap-4 text-center">

					{{-- ATC Rating --}}
					<div class="flex items-center gap-2">
						{!! $ratingBadge($user->qualification_atc->code ?? null) !!}
						<span class="text-xs font-semibold whitespace-nowrap">
							{{ $user->qualification_atc->name_long ?? 'No ATC Rating' }}
						</span>
					</div>

					<div class="h-5 w-px bg-gray-300 dark:bg-gray-600"></div>

					{{-- Pilot Rating --}}
					<div class="flex items-center gap-2">
						{!! $ratingBadge($pilotRating?->code ?? null) !!}
						<span class="text-xs font-semibold whitespace-nowrap">
							{{ $pilotRating?->name_long ?? 'No Pilot Rating' }}
						</span>
					</div>

				</div>

			</div>

		</div>

	</x-filament::section>
</x-filament-widgets::widget>
