<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <x-filament::button type="submit" class="my-2">Submit</x-filament::button>
    </form>

    <div class="items-center" wire:loading.flex>
        <x-filament::loading-indicator class="w-10 mx-auto"/>
    </div>
    @if($this->statistics)
        <x-filament::card wire:loading.remove>
            <table class="table text-center table-auto w-full">
                <tbody>

                @foreach($this->statistics as $groupName => $statisticGroup)
                    <td colspan="2" class="text-left"><strong>{{ $groupName }}<br></strong></td>
                    @foreach($statisticGroup as $statistic)
                        <tr>
                            <td>{{ $statistic['name'] }}</td>
                            <td> {{ $statistic['value'] }}</td>
                        </tr>
                    @endforeach

                @endforeach

                </tbody>
            </table>
        </x-filament::card>
    @endif
</x-filament::page>
