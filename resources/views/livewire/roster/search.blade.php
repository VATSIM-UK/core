<x-slot name="title">Search Roster</x-slot>
<div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
    <div class="bg-white px-6 py-12 shadow space-y-6 rounded-lg sm:px-12">
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
            <a wire:navigate href="{{ route('site.roster.index') }}"
               class="text-bold text-blue-500 hover:cursor-pointer">Go back</a>
        </div>
    </div>
</div>
