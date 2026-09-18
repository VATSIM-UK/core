<x-filament-widgets::widget>
    @if ($session && $startsAt && $endsAt)
        <div
            class="rounded-2xl border border-primary-200/70 bg-primary-50/80 px-5 py-4 dark:border-primary-500/20 dark:bg-primary-500/10"
        >
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                        Upcoming session:
                        <span class="font-bold">
                            {{ $session->position ?? 'Mentoring' }}
                        </span>
                    </h3>

                    @if ($remainingSessions > 0)
                        <span class="rounded-full bg-white/70 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-100">
                            +{{ $remainingSessions }}
                            more {{ \Illuminate\Support\Str::plural('session', $remainingSessions) }}
                        </span>
                    @endif
                </div>

                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                    <time datetime="{{ $startsAt->toIso8601String() }}">
                        {{ $startsAt->format('D d M') }}
                    </time>

                    <span class="text-gray-500 dark:text-gray-400">&bull;</span>

                    <span>
                        {{ $startsAt->format('H:i') }}Z – {{ $endsAt->format('H:i') }}Z
                    </span>

                    <span class="text-gray-500 dark:text-gray-400">&bull;</span>

                    <span>
                        Mentor:
                        <span class="font-semibold">
                            {{ $session->mentor?->name ?? 'Unknown' }}
                        </span>
                    </span>
                </div>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>