<x-filament-panels::page>
    {{ $this->infolist }}

    @if (!$this->practicalResult->examBooking->isPilotExam())
        <h1 class="text-xl font-bold">Grades</h1>

        {{ $this->criteriaInfoList }}
    @endif
</x-filament-panels::page>