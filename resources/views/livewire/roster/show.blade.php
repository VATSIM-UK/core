<x-slot name="title">Roster for {{ $account->id }}</x-slot>
<div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px] max-h-full">
    <div class="bg-white shadow rounded-lg overflow-hidden flex flex-col max-h-full">
        <div class="max-h-full overflow-y-auto space-y-6 sm:px-12 px-6 py-12">
            <header class="flex flex-col md:flex-row md:justify-between items-center">
                <div class="inline-flex flex-col md:items-start items-center">
                    <span class="font-bold text-2xl">{{ $account->id }}</span>
                    <div class="opacity-50">
                        <span>{{ $account->qualification_atc }} - </span>
                        <span>{{ $account->primary_state->name }} Member</span>
                    </div>
                </div>
                <div class="ml-2">
                    @if($roster)
                        <span
                            class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-md font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active on Roster</span>
                    @else
                        <span
                            class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-md font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive on Roster</span>
                    @endif
                </div>
            </header>

            <div class="space-y-2 overflow-auto">
                <div class="flex flex-col space-y-8">
                    <div class="flex flex-col items-start space-y-1">
                        @foreach($account->endorsements()->active()->get()->groupBy('type') as $type => $endorsements)
                            <span class="text-sm font-semibold">{{ $type }}</span>
                            @foreach($endorsements as $endorsement)
                                <span>{{ $endorsement->endorsable?->name ?? 'Unknown Endorsement' }}
                                    @if($endorsement->expires())
                                        <span
                                            class="text-xs opacity-75">Expires {{ $endorsement->expires_at->toFormattedDateString() }}</span>
                                    @endif
                                            </span>
                                <span
                                    class="text-xs text-left opacity-50">Covers: {{ $endorsement->endorsable?->description ?? 'No description available' }}</span>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex flex-col space-y-4">
                @if($roster && $roster->restrictionNote == null)
                    <hr>
                    <form wire:submit="search" class="flex flex-col mb-4 space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Check
                                Position</label>
                            <div class="mt-2">
                                <input wire:model="searchTerm" id="search" name="search" type="text" autocomplete="off"
                                    required
                                    placeholder="e.g. EGKK or EGKK_APP"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="flex w-full justify-center rounded-md bg-brand px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-sky-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Search
                            </button>
                        </div>
                    </form>
                @endif
                @if($positions)
                    @if(count($positions) == 1)
                    <span>
                        {{ $roster->accountCanControl($positions[0])
                            ? "✅ $account->id can control " .$positions[0]->callsign."."
                            : "❌ $account->id cannot control " .$positions[0]->callsign."."
                        }}
                    </span>
                    @else
                        <table class="table-auto">
                            <tr>
                                <td></td>
                                <td><i>Can Control</i></td>
                            </tr>
                            @foreach($positions as $position)
                                <tr class="odd:bg-blue-100">
                                    <th>{{$position->callsign}}</th>
                                    <td>{{$roster->accountCanControl($position) ? '✅' : '❌'}}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                @endif
                @if(!$roster)
                    <span>❌ {{ $account->id }} cannot control any UK positions.</span>
                @endif
                @if($roster && $roster->restrictionNote)
                    <span class="text-red-500">❗ {{ $roster->restrictionNote->content }}</span>
                @endif
            </div>

            <div>
                <a class="text-bold text-blue-500 hover:cursor-pointer" wire:navigate
                href="{{ route('site.roster.search') }}">Go back</a>
            </div>
        </div>
    </div>
</div>
