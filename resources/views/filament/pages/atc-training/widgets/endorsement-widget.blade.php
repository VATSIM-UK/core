<x-filament::widget>
    <x-filament::card>
        <h2 class="text-xl font-bold">{{$this->position}} Endorsements</h2>
        <p class="text-sm text-gray-400 mb-2">All Active Endorsements</p>
        <div class="mt-4">
            @php 
            $currentRating = null;
            $rows = $this->getRows();

             @endphp
            @foreach ($rows as $row)
                @if ($currentRating !== $row['rating'])
                @php $currentRating = $row['rating']; @endphp
                <h3 class="text-lg font-semibold mt-4 border-b">{{ $currentRating }}</h3>
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr>
                            <th class="text-left"> Endorsement</th>
                            <th class="text-left">Count</th>
                        </tr>
                    </thead>
                
                <tbody>
                @endif
                <tr>
                    <td>{{ $row['endorsement'] }}</td>
                    <td>{{ $row['count'] }}</td>
                </tr>
                @if (!isset($rows[$loop->index + 1]) || $rows[$loop->index + 1]['rating'] !== $currentRating)
                </tbody>
                </table>
                @endif
            @endforeach
        </div>

        <p class="text-sm text-right font-semibold">Total Endorsements: {{ $this->getEndorsementCount() }}</p>
    </x-filament::card>
</x-filament::widget>
