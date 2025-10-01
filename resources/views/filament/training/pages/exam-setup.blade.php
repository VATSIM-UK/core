<x-filament-panels::page>
    <form wire:submit.prevent="setupExamOBS">
        {{ $this->formOBS }}

        <div class="flex justify-end">
            <x-filament::button type="submit" class="my-4 bg-brand">Setup exam</x-filament::button>
        </div>
    </form>

    <form wire:submit.prevent="setupExam">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" class="my-4 bg-brand">Setup exam</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
