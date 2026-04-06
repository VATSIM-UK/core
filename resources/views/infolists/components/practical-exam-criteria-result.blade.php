<x-filament-infolists::components.entry-wrapper :entry="$entry">
    <div class="flex items-center justify-center w-full px-3 py-1.5 text-xs font-semibold rounded-md uppercase tracking-wide shadow-xs transition-all duration-200 hover:shadow-md"
         style="background-color: {{ $getColorForResult() }}; color: {{ $getTextColorForResult() }};">
        <span class="leading-none">{{ $getRecord()->resultHuman }}</span>
    </div>
</x-filament-infolists::components.entry-wrapper>
