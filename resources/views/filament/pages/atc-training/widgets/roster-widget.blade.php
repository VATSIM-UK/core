<x-filament::widget>
    <x-filament::card>
        <h2 class="text-xl font-bold">ATC Roster</h2>
        <p class="text-sm text-gray-400 mb-2">All Active Members</p>
        <div class="mt-4">
            @foreach ($this->getRows() as $row)
                <div class="flex justify-between border-b">
                    <span>{{ $row['rating'] }}</span>
                    <span>{{ $row['count'] }}</span>
                </div>
            @endforeach
        </div>

        <p class="text-sm text-right font-semibold mt-5">Total: {{ $this->getTotalCount() }}</p>
    </x-filament::card>
</x-filament::widget>