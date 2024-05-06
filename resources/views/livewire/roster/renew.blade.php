<x-slot name="title">Renew Roster Currency</x-slot>
<main>
    @if ($page == 1)
    <div class="mt-2 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="bg-white px-4 py-2 shadow space-y-6 sm:rounded-lg sm:px-12">
            <div>
                <span class="text-2xl font-bold">👋 Hello, {{ auth()->user()->name_first }}!</span>
            </div>
            @if (!$canReactivate)
                <span>As it has been 18 months since your last ATC session you cannot automatically reactivate your roster membership.
                    <br>
                    Please <a class="text-blue-500 hover:cursor-pointer" href="mailto:atc-training@vatsim.uk">contact ATC Training</a>.
                </span>
            @else
                <p>It has been a while! Our records show it has {{ $lastLogon }} since your last controlling session.</p>
                <p>Because of this, you are required to reactivate onto the VATSIM UK controlling roster.</p>
                <p>Before you do this, please take a read through what has changed in the Division and Procedurally whilst you've been gone!</p>
            @endif
        </div>
    </div>
    @if ($canReactivate)
    <div class="mt-2 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="bg-white px-4 py-2 shadow space-y-6 sm:rounded-lg sm:px-12">

        <span class="text-2xl font-bold">Notifications</span>
        <p>There have been a few changes since you have been gone! Please take the time
            to read through the below notifications, acknowleding you have read them as you
            read through them.</p>
        <p>Click on the arrow or the title of the notification to see more and mark as read.</p>
        <p>You have {{ count($notifications) }} notifications to read.</p>
            <button x-bind:disabled="{{ $this->reactivateButtonDisabled }}" class="p-2 disabled:opacity-25 bg-brand text-white rounded-lg shadow" wire:click="nextPage">Reactivate</button>
        </p>
        @if ($notifications->count() > 0)
        <div class="h-48 overflow-scroll">
            @foreach ($notifications as $notification)
                <div class="text-left" x-data="{ expanded: false }">
                    <span x-on:click="expanded = ! expanded" class="text-md font-semibold hover:cursor-pointer">{{ $notification['title'] }}</span>
                    <button x-on:click="expanded = ! expanded">⬇️</button>
                    <div x-show="expanded" x-collapse>
                        <p>{{ $notification['body'] }}</p>
                        @if ($notification['link'])
                            <a href="{{ $notification['link'] }}" target="_blank" lass="text-blue-500 hover:cursor-pointer">Read more</a>
                        @endif
                        <p class="text-blue-900 cursor-pointer" wire:click="markNotificationRead({{ $notification['id'] }}, {{ $notification['id'] }})">Mark read</p>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
        @endif
    </div>
    <div class="mt-6">
    @endif
    </div>
    @elseif ($page === 2)
        <div class="mt-2 sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="bg-white px-4 py-2 shadow space-y-6 sm:rounded-lg">

            <span class="text-2xl font-bold">Reactivate</span>
            <div class="mt-6">
                <p>Once you are added back onto the roster you must maintain a minimum of 3 hours controlling any UK position
                    within a calendar quarter e.g. January -> March.</p>
                <p>Click the button below to add yourself back onto the roster.</p>
                <button class="mt-2 p-2 bg-brand text-white rounded-lg shadow" x-on:click="$wire.reactivate()">Add to roster</button>
            </div>
            </div>
        </div>
    @endif
</main>
