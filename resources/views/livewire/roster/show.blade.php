<div class="flex w-screen h-screen items-center justify-center text-center bg-gray-200">
    <div class="flex min-h-full w-full md:w-2/3 flex-col justify-center py-12 px-6 lg:px-8">
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="bg-white px-6 py-12 shadow space-y-6 rounded-lg sm:px-12">

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
                @if($account->endorsements()->active()->count())
                    <div class="flex flex-col space-y-8">
                        <div class="flex flex-col items-start space-y-1">
                            @foreach($account->endorsements()->active()->get()->groupBy('type') as $type => $endorsements)
                                <span class="text-sm font-semibold">{{ $type }} endorsements</span>
                                @foreach($endorsements as $endorsement)
                                    <span>{{ $endorsement->endorsable->name() }}
                                        @if($endorsement->expires())
                                            <span
                                                class="text-xs opacity-75">Expires {{ $endorsement->expires_at->toFormattedDateString() }}</span>
                                        @endif
                                        </span>
                                    <span
                                        class="text-xs opacity-50">Covers: {{ $endorsement->endorsable->description() }}</span>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <a class="text-bold text-blue-500 hover:cursor-pointer" wire:navigate
                       href="{{ route('site.roster.search') }}">Go back</a>
                </div>
            </div>
        </div>
    </div>
</div>
