<x-slot name="title">Failure</x-slot>
<main>
    <div class="bg-red-50 border-red-600 border-2 rounded mt-2 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="shadow space-y-6 sm:rounded-lg sm:px-4">
            @if (session('failReason'))
                <p class="py-4 text-left">❌ Fail! Your waiting list retention check has failed due to the following
                    reason:
                    <span class="font-bold">{{ session('failReason') }}</span>
                </p>
            @else
                <p class="py-4 text-left">❌ Fail! Your waiting list retention check has failed, but no specific reason
                    was
                    provided.</p>
            @endif
        </div>
    </div>
</main>
