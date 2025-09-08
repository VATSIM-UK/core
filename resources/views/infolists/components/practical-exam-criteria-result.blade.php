<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="flex items-center justify-center w-full px-3 py-1.5 text-xs font-semibold rounded-md uppercase tracking-wide shadow-sm transition-all duration-200 hover:shadow-md"
         style="background-color: {{ $getColorForResult() }}; color: {{ $getTextColorForResult() }};">
        <span class="leading-none">{{ $getRecord()->resultHuman }}</span>
    </div>
</x-dynamic-component>
