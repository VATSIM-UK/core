<x-slot name="title">Roster</x-slot>
<div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
    <div class="bg-white px-6 py-12 shadow space-y-6 sm:rounded-lg sm:px-12">
        <div class="flex flex-col items-center space-y-8">
            <div class="flex flex-col items-center space-y-4">
                <span class="text-2xl font-bold">👋 Hello, {{ auth()->user()->name_first }}!</span>
                <div>
                    @if($roster)
                        <span
                            class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-md font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active on Roster</span>
                    @else
                        <span
                            class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-md font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive on Roster</span>
                    @endif
                </div>
                @if($roster)
                    <span>
                        You are currently <span class="font-bold">active</span> on the VATSIM UK roster and can control any positions
                        <a class="text-blue-500 hover:cursor-pointer" href="{{ route('site.roster.show', ['account' => auth()->user()]) }}">listed on your roster page</a>.
                    </span>
                @elseif(auth()->user()->hasState('DIVISION'))
                    <span>
                        You are currently <span class="font-bold">inactive</span> on the VATSIM UK roster, and cannot control any UK positions until you
                        <a class="text-blue-500 hover:cursor-pointer" href="{{ route('site.roster.renew') }}">renew your currency</a>.
                    </span>
                @else
                    <span>
                        You are currently <span class="font-bold">inactive</span> on the VATSIM UK roster, and cannot control any UK positions.
                        <br>
                        Please <a class="text-blue-500 hover:cursor-pointer" href="mailto:member-services@vatsim.uk">contact Member Services</a> if you believe this is incorrect.
                    </span>
                @endif
            </div>
            <div class="flex flex-col">
                @if(!$roster && auth()->user()->hasState('DIVISION'))
                    <a wire:navigate href="{{ route('site.roster.renew') }}"
                       class="text-bold text-blue-500 hover:cursor-pointer">Renew my currency</a>
                @endif
                <a wire:navigate href="{{ route('site.roster.search') }}"
                   class="text-bold text-blue-500 hover:cursor-pointer">Search the roster</a>
                </div>
        </div>
    </div>
</div>
