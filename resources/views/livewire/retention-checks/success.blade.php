<x-slot name="title">Success</x-slot>
<main>
    <div
        class="bg-green-50 ring-1 ring-inset ring-green-600/20 border-green-100 border-2 rounded mt-2 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="shadow space-y-6 rounded-lg sm:px-4">
            <p class="text-green-700 text-left py-4">🎉 Success! Waiting list retention check has been completed
                successfully.</p>
            @if (session('extraMessage'))
                <p class="text-green-700 text-left pb-4">{{ session('extraMessage') }}</p>
            @endif
        </div>
    </div>
</main>
