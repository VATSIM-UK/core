<x-filament-widgets::widget>
    <x-filament::section>

        <div class="relative">

            {{-- Active / Inactive badge --}}
            <span class="px-4 py-1.5 text-xs font-semibold rounded-full absolute sm:right-6 sm:top-1 top-10 right-3
                {{ $rosterStatus
                    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                    : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200'
                }}">
                {{ $rosterStatus ? 'Active' : 'Inactive' }}
            </span>

            {{-- Header --}}
            <div class="flex flex-col">
                <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    Welcome back, {{ $user->name }}
                </h2>
            </div>

            <div class="space-y-3 mt-5">

                {{-- Main grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-8 text-sm">

                    <div>
                        <span class="text-gray-500">CID</span>
                        <div class="font-medium">{{ $user->id }}</div>
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

                {{-- Endorsements --}}
                <div>
                    <span>Endorsements</span>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($endorsements->isEmpty())
                            <span class="text-gray-400 text-sm">None</span>
                        @else
                            @foreach ($endorsements as $endorsement)
                                <span class="px-2.5 py-1 text-xs rounded-md bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200 shadow-sm">
                                    {{ $endorsement }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Roles --}}
                <div class="mt-3">
                    <span>Roles</span>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($roles->isEmpty())
                            <span class="text-gray-400 text-sm">None</span>
                        @else
                            @foreach ($roles as $role)
                                <span class="px-2.5 py-1 text-xs rounded-md bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200 shadow-sm">
                                    {{ $role }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>

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
