<x-filament::card class="flex flex-col gap-4">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 dark:border-white/10 pb-4">
        <div class="flex items-center gap-2">
            <x-filament::button color="gray" wire:click="previousDay" icon="heroicon-m-chevron-left" class="!px-2" />
            
            <x-filament::button color="gray" wire:click="setToday">Today</x-filament::button>

            <x-filament::input.wrapper>
                <x-filament::input type="date" wire:model.live="date" class="min-w-[140px]"/>
            </x-filament::input.wrapper>

            <x-filament::button color="gray" wire:click="nextDay" icon="heroicon-m-chevron-right" icon-alias="next" class="!px-2" />

            <div class="ml-4 border-l pl-4 border-gray-200 dark:border-white/10">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="category">
                        <option value="">All Training Groups</option>
                        @foreach($this->availableCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>
    </div>

    {{-- Mobile device warning --}}
    {{-- Will stop the loading of the gantt chart on mobile because the styling all breaks --}}
    <div class="md:!hidden flex flex-col items-center justify-center py-12 px-4 text-center bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-white/10">
        <x-filament::icon icon="heroicon-o-computer-desktop" class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Desktop Required</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
            The availability planner is currently only available for wider screens. Please view this page on a tablet or desktop device to view.
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">We are actively working on allowing mobile devices to access this functionality.</p>
    </div>

    {{-- Main Chart --}}
    <div class="hidden lg:block overflow-x-auto overflow-y-auto max-h-[600px] relative rounded-lg border border-gray-200 dark:border-white/10 custom-scrollbar">

        <div class="min-w-[1000px]">
            <div class="flex sticky top-0 bg-gray-50 dark:bg-gray-800/95 backdrop-blur-sm z-30 shadow-sm border-b border-gray-200 dark:border-white/10">
                <div class="w-64 flex-shrink-0 sticky left-0 z-40 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-white/10 px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                    Students
                </div>
                
                <div class="flex-1 flex relative">
                    @foreach($hours as $hour)
                        <div class="flex-1 border-r border-gray-200 dark:border-white/5 text-xs text-center text-gray-500 font-medium py-3">
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

                @forelse($students as $student)
                    <div class="flex group hover:bg-gray-50/50 dark:hover:bg-white/5 transition duration-75 relative min-h-[72px]">
                        
                        <div class="w-64 flex-shrink-0 sticky left-0 z-20 bg-inherit border-r border-gray-200 dark:border-white/10 px-4 py-3 flex flex-col justify-center">
                            
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-medium text-sm text-gray-950 dark:text-white truncate">
                                    {{ $student->name }}
                                </div>
                                @if($student->pending_position)
                                    <x-filament::badge color="gray" size="sm">
                                        {{ $student->pending_position }}
                                    </x-filament::badge>
                                @endif
                            </div>
                            
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $student->cid }}
                            </div>
                            
                            <div class="text-xs text-gray-300 dark:text-gray-400 mt-0.5">
                                Last Session: {{ $student->last_session_date ? \Carbon\Carbon::parse($student->last_session_date)->diffForHumans(null, true)." ago" : 'Never' }}
                            </div>
                        </div>

                        <div class="flex-1 relative">
                            <div class="absolute inset-0 flex pointer-events-none">
                                @foreach($hours as $hour)
                                    <div class="flex-1 border-r border-gray-100 dark:border-white/[0.02]"></div>
                                @endforeach
                            </div>

                            @foreach($student->availabilities as $avail)
                                @php
                                    $start = \Carbon\Carbon::parse($avail->from);
                                    $end = \Carbon\Carbon::parse($avail->to);
                                    
                                    $startMinutesFromMidnight = ($start->hour * 60) + $start->minute;
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
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <x-filament::icon icon="heroicon-o-calendar" class="mx-auto h-8 w-8 mb-3 text-gray-400"/>
                        No availability found for this date.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament::card>