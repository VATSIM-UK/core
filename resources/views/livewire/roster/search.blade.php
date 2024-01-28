<div class="flex w-screen h-screen items-center justify-center text-center bg-gray-200">
    <div class="flex min-h-full w-full md:w-1/2 flex-col justify-center py-12 px-6 lg:px-8">
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="bg-white px-6 py-12 shadow space-y-6 rounded-lg sm:px-12">
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
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-md font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-md font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive</span>
                            @endif
                        </div>
                    </header>
                    <main>
                    </main>
                    <div class="flex flex-col space-y-8">
                        @if($account->permanentEndorsements)
                            <div class="flex flex-col items-start">
                                <span class="font-bold">Additional endorsements</span>
                                @foreach($account->permanentEndorsements as $accountEndorsement)
                                    <span>{{ $accountEndorsement->positionGroup->description }}</span>
                                    <div class="text-xs opacity-50">
                                        {{ implode(', ', $accountEndorsement->positionGroup->positions->map(
                                            fn($position) => $position->callsign
                                        )->toArray()
                                        ) }}
                                    </div>
                                @endforeach
                                @foreach($account->temporaryEndorsements as $accountEndorsement)
                                	<div class="flex items-baseline">
                                        <span class="italic">{{ $accountEndorsement->positionGroup->description }} </span>
                                        <span class="ml-1 text-xs opacity-75">(Expires {{ $accountEndorsement->expired_at->toFormattedDateString() }})</span>
                                    </div>
                                    <div class="text-xs opacity-50">
                                        {{ implode(', ', $accountEndorsement->positionGroup->positions->map(
                                            fn($position) => $position->callsign
                                        )->toArray()
                                        ) }}
                                    </div>
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
