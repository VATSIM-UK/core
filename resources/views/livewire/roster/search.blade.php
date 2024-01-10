<div class="flex w-screen h-screen items-center justify-center text-center bg-gray-200">
    <div class="flex min-h-full w-1/2 flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="bg-white px-6 py-12 shadow space-y-6 sm:rounded-lg sm:px-12">
                @if(! $account)
                    <form wire:submit="search" class="flex flex-col space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">VATSIM CID</label>
                            <div class="mt-2">
                                <input wire:model="searchTerm" id="search" name="search" type="search" autocomplete="off" required
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
                    <div>
                        <a wire:navigate href="{{ route('site.roster.index') }}" class="text-bold text-blue-500 hover:cursor-pointer">Go back</a>
                    </div>
                @else
                    <div class="flex flex-col items-center space-y-8">
                        <span class="font-bold">{{ $account->id }}</span>
                        <div class="flex flex-col items-center">
                            <span>{{ $account->qualification_atc }}</span>
                            <span>{{ $account->primary_state->name }} Member</span>
                            <span>{{ $roster ? 'On Roster' : 'Not on Roster' }}</span>
                        </div>
                        @if($account->endorsements->whereNull('expired_at')->isNotEmpty())
                        	{{-- TODO: use relationsip or type --}}
                            <div class="flex flex-col items-center">
                                <span class="font-bold">Perm Endorsements</span>
                                @foreach($account->endorsements->whereNull('expired_at') as $accountEndorsement)
                                    <span>{{ $accountEndorsement->positionGroup->description }}</span>
                                    @foreach($accountEndorsement->positionGroup->positions as $position)
                                        <span>{{ $position->callsign }}</span>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif

                        @if($account->endorsements->whereNotNull('expired_at')->isNotEmpty())
                            <div class="flex flex-col items-center">
                                <span class="font-bold">Temp Endorsements</span>
                                @foreach($account->endorsements->whereNotNull('expired_at') as $accountEndorsement)
                                    <span>{{ $accountEndorsement->positionGroup->description }}</span>
                                    @foreach($accountEndorsement->positionGroup->positions as $position)
                                        <span>{{ $position->callsign }}</span>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                        <div>
                            <a class="text-bold text-blue-500 hover:cursor-pointer" wire:click="clear">Go back</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
