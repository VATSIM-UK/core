<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <x-filament::button type="submit" class="my-2">Submit</x-filament::button>
    </form>

    <div class="items-center" wire:loading.flex>
        <x-filament::loading-indicator class="w-10 mx-auto" />
    </div>
    @if ($this->statistics)
        <x-filament::card wire:loading.remove>
            <table class="table text-center table-auto w-full">
                <tbody>

                    @foreach ($this->statistics as $groupName => $statisticGroup)
                        <td colspan="2" class="text-left"><strong>{{ $groupName }}<br></strong></td>
                        @foreach ($statisticGroup as $statistic)
                            <tr>
                                <td>{{ $statistic['name'] }}</td>
                                <td> {{ $statistic['value'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                </tbody>
            </table>
        </x-filament::card>
        <x-filament::button wire:click="exportSessionsCsv" type="submit">
            Export Session CSV
        </x-filament::button>
    @endif


    <script>
        document.addEventListener('alpine:init', () => {
            window.addEventListener('download-csv', event => {
                const csv = event.detail.csv;
                const filename = event.detail.filename;
                const blob = new Blob([csv], {
                    type: 'text/csv'
                });
                const url = URL.createObjectURL(blob);

                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                URL.revokeObjectURL(url);
            });
        });
    </script>
</x-filament::page>
