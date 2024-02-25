<x-slot name="title">Roster</x-slot>
<div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
    <div class="bg-white px-6 py-12 shadow space-y-6 sm:rounded-lg sm:px-12">
        <div class="flex flex-col items-center space-y-8">
            <div class="flex flex-col items-center space-y-4">
                <p>Something about whether the logged in user is on the roster.</p>
                <div>
                    <a wire:navigate href="{{ route('site.roster.renew') }}"
                        class="text-bold text-blue-500 hover:cursor-pointer">Renew my currency</a>
                     |
                    <a wire:navigate href="{{ route('site.roster.search') }}"
                        class="text-bold text-blue-500 hover:cursor-pointer">Search the roster</a>
                </div>
            </div>
        </div>
    </div>
</div>
