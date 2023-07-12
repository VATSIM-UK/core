<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    
        <x-filament-support::button  type="submit" class="my-6">Submit</x-filament-support::button>
    </form>

    @if($this->statistics)
    <x-filament::card heading="Results for {{$this->quarterMappings[$this->quarter]}} {{$this->year}}" wire:loading.remove>
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
