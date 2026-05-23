<x-filament::card class="flex flex-col gap-4">

    {{-- Toolbar --}}
    <div class="flex flex-col md:flex-row items-center gap-3 border-gray-200 dark:border-white/10 pb-4">
        <div class="flex flex-wrap md:flex-nowrap items-center justify-center gap-2">
            <x-filament::button color="gray" wire:click="previousDay" icon="heroicon-m-chevron-left" class="!px-2" />
            <x-filament::button color="gray" wire:click="setToday">Today</x-filament::button>
            <x-filament::input.wrapper>
                <x-filament::input type="date" wire:model.live="date" class="min-w-[140px]" />
            </x-filament::input.wrapper>
            <x-filament::button color="gray" wire:click="nextDay" icon="heroicon-m-chevron-right" icon-alias="next"
                class="!px-2" />

            <x-filament::input.wrapper class="ml-4">
                <x-filament::input.select wire:model.live="category">
                    <option value="">All Training Groups</option>
                    @foreach ($this->availableCategories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>

    {{-- Chart --}}
    @if ($students->isEmpty())
        <div class="p-8 text-center border-t border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-calendar" class="mx-auto h-8 w-8 mb-3 text-gray-400" />
            No availability found for this date.
        </div>
    @else
        <div class="hidden lg:!block">
            @include('livewire.training.availability-gantt-desktop', ['students' => $this->pagedStudents])
        </div>
        <div class="block lg:!hidden">
            @include('livewire.training.availability-gantt-mobile', ['students' => $this->pagedStudents])
        </div>

        {{-- Page Arrows --}}
        @if ($students->count() > $this->studentsPerPage)
            <div class="flex items-center justify-end gap-2 pt-2 border-gray-200 dark:border-white/10">
                <x-filament::button color="gray" wire:click="previousStudentsPage" icon="heroicon-m-chevron-left"
                    class="!px-2" :disabled="$studentsPage <= 1" />
                <x-filament::button color="gray" wire:click="nextStudentsPage" icon="heroicon-m-chevron-right"
                    class="!px-2" :disabled="$studentsPage * $this->studentsPerPage >= $students->count()" />
            </div>
        @endif
    @endif

    <x-filament-actions::modals />
</x-filament::card>
