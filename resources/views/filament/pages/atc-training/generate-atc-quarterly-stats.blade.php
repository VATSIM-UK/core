<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <x-filament::button type="submit" class="my-2">Submit</x-filament::button>
    </form>

    <div class="items-center" wire:loading.flex>
        <x-filament::loading-indicator class="w-10 mx-auto"/>
    </div>
    @if($this->statistics)
    <h1 class="text-2xl font-bold">Stats For Quarter Selected</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6" wire:loading.remove>
                @foreach($this->statistics as $groupName => $statisticGroup)
                <x-filament::card>
                    <h2 class="text-xl font-bold">{{$groupName}}</h2>
                    @foreach($statisticGroup as $statistic)
                    <div class="flex justify-between border-b py-2">  
                            <span>{{ $statistic['name'] }}</span>
                            <span> 
                                @if (is_string($statistic['value']) && str_starts_with($statistic['value'], 'http'))
                                    <a href="{{ $statistic['value'] }}" target="_blank" class="bg-primary-500 text-white px-3 py-1 rounded-md hover:bg-primary-600">
                                        View Update
                                    </a>
                                    @else
                                    {{ $statistic['value'] }}
                                @endif
                            </span>
                        </div> 
                        
                    @endforeach
</x-filament::card>
                @endforeach

                </tbody>
            </table>
</div>
    @endif
</x-filament::page>
