<div
    class="overflow-y-auto overflow-x-hidden max-h-[600px] relative rounded-lg border border-gray-200 dark:border-white/10 custom-scrollbar">
    <div class="w-full">
        <div
            class="flex sticky top-0 bg-gray-50 dark:bg-gray-800/95 backdrop-blur-sm z-30 shadow-sm border-b border-gray-200 dark:border-white/10">
            <div
                class="w-64 flex-shrink-0 sticky left-0 z-40 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-white/10 px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                Students
            </div>

            <div class="flex-1 flex relative">
                @foreach ($hours as $hour)
                    <div
                        class="flex-1 border-r border-gray-200 dark:border-white/5 text-xs text-center text-gray-500 font-medium py-3">
                        {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col relative divide-y divide-gray-200 dark:divide-white/5">
            @php
                $firstHour = $hours[0];
                $totalHours = count($hours);
                $totalTimelineMinutes = $totalHours * 60;
            @endphp

            @foreach ($students as $student)
                <div
                    class="flex group hover:bg-gray-50/50 dark:hover:bg-white/5 transition duration-75 relative min-h-[72px]">

                    <div
                        class="w-64 flex-shrink-0 sticky left-0 z-20 bg-inherit border-r border-gray-200 dark:border-white/10 px-4 py-3 flex flex-col justify-center">
                        <div class="flex items-start justify-between gap-2">
                            <div class="font-medium text-sm text-gray-950 dark:text-white truncate">
                                {{ $student->name }}
                            </div>
                            @if ($student->pending_position)
                                <x-filament::badge color="gray" size="sm">
                                    {{ $student->pending_position }}
                                </x-filament::badge>
                            @endif
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $student->cid }}
                        </div>

                        <div class="text-xs text-gray-300 dark:text-gray-400 mt-0.5">
                            Last Session:
                            {{ $student->last_session_date ? \Carbon\Carbon::parse($student->last_session_date)->diffForHumans(null, true) . ' ago' : 'Never' }}
                        </div>
                    </div>

                    <div class="flex-1 relative">
                        <div class="absolute inset-0 flex pointer-events-none">
                            @foreach ($hours as $hour)
                                <div class="flex-1 border-r border-gray-100 dark:border-white/[0.02]"></div>
                            @endforeach
                        </div>

                        @foreach ($student->availabilities as $avail)
                            @php
                                $start = \Carbon\Carbon::parse($avail->from);
                                $end = \Carbon\Carbon::parse($avail->to);

                                $startMinutesFromMidnight = $start->hour * 60 + $start->minute;
                                $timelineStartMinutes = $firstHour * 60;

                                $relativeStartMinutes = max(0, $startMinutesFromMidnight - $timelineStartMinutes);
                                $durationMinutes = $start->diffInMinutes($end);

                                $leftPercent = ($relativeStartMinutes / $totalTimelineMinutes) * 100;
                                $widthPercent = ($durationMinutes / $totalTimelineMinutes) * 100;
                            @endphp

                            <div class="absolute top-2 bottom-2 flex items-center justify-center px-1.5 rounded-md shadow-sm opacity-90 transition-all border group/block ring-1 bg-success-500 hover:bg-success-600 border-success-600 dark:border-success-400 ring-success-500/30 overflow-hidden"
                                style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%;">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
