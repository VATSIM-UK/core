<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    
        <x-filament::button type="submit" class="my-2">Submit</x-filament::button>
    </form>

    <div class="items-center" wire:loading.flex><x-filament::loading-indicator class="w-10 mx-auto" /></div>
    @if($this->statistics)
    <x-filament::card wire:loading.remove>
        <x-filament::section.heading>Results for {{$this->quarterMappings[$this->quarter]}} {{$this->year}}</x-filament.section::heading>
        <table class="table text-center table-auto w-full">
            <thead>
            <tr>
                <th>Statistic</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>

            @foreach($this->statistics as $statistic)
                <tr>
                    <td>{{ $statistic['name'] }}</td>
                    <td>{{ $statistic['value'] }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </x-filament::card>
    @endif
</x-filament::page>
