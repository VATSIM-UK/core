<x-filament::card class="flex flex-col gap-4">

    {{-- Shared Header Toolbar --}}
    <div class="flex flex-col md:flex-row items-center gap-3 border-gray-200 dark:border-white/10 pb-4">

        {{-- Date Navigation --}}
        <div class="flex flex-wrap md:flex-nowrap items-center justify-center gap-2">
            <x-filament::button color="gray" wire:click="previousDay" icon="heroicon-m-chevron-left" class="!px-2" />
            <x-filament::button color="gray" wire:click="setToday">Today</x-filament::button>
            <x-filament::input.wrapper>
                <x-filament::input type="date" wire:model.live="date" class="min-w-[140px]"/>
            </x-filament::input.wrapper>
            <x-filament::button color="gray" wire:click="nextDay" icon="heroicon-m-chevron-right" icon-alias="next" class="!px-2" />

            <x-filament::input.wrapper class="ml-4">
                <x-filament::input.select wire:model.live="category">
                    <option value="">All Training Groups</option>
                    @foreach($this->availableCategories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

    </div>

    {{-- Render Workspace Layouts --}}
    @if($students->isEmpty())
        <div class="p-8 text-center border-t border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-calendar" class="mx-auto h-8 w-8 mb-3 text-gray-400"/>
            No availability found for this date.
        </div>
    @else
        <div class="hidden lg:!block">
            @include('livewire.training.availability-gantt-desktop')
        </div>
        <div class="block lg:!hidden">
            @include('livewire.training.availability-gantt-mobile')
        </div>
    @endif

</x-filament::card>