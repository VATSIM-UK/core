<div class="flex w-screen h-screen items-center justify-center text-center bg-gray-200">
    <div class="flex min-h-full w-1/2 flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="bg-white px-6 py-12 shadow space-y-6 sm:rounded-lg sm:px-12">
                    <div class="flex flex-col items-center space-y-8">
                        <div class="flex flex-col items-center">
                            <p>Something about whether the logged in user is on the roster.</p>
                            <a wire:navigate href="{{ route('site.roster.renew') }}" class="text-bold text-blue-500 hover:cursor-pointer">Renew my currency</a>
                            <a wire:navigate href="{{ route('site.roster.search') }}" class="text-bold text-blue-500 hover:cursor-pointer">Search the roster</a>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

