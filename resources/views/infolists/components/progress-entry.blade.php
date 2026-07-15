{{-- blade-formatter-disable --}}
<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        // Config Values
        $config = [
            // The 4 scoreable segments and their tooltip labels, score values below 2 are not shown and arrows are not drawn.
            'segments' => [2, 3, 4, 5],
            'labels' => [
                2 => 'Covered',
                3 => 'Developing',
                4 => 'Good',
                5 => 'Test Standard',
            ],

            // Outer wrapper
            'containerClasses' => 'flex flex-col w-full max-w-xs gap-2 font-sans select-none',
            'trackClasses' => 'relative flex w-full h-6 bg-zinc-100 dark:bg-[#141416] rounded-lg overflow-hidden',

            // Header row: "Progress" label + delta indicator
            'headerWrapperClasses' => 'flex items-end justify-between px-1',
            'headerLabel' => 'Progress',
            'headerLabelClasses' => 'text-base font-normal text-gray-400 dark:text-gray-200',
            'deltaClasses' => 'text-base font-medium tracking-wide',
            'deltaColorPositive' => 'text-emerald-600 dark:text-emerald-500',
            'deltaColorNegative' => 'text-red-500 dark:text-red-400',
            'deltaColorNeutral' => 'text-gray-400',

            // Segment borders
            'segmentBorders' => [
                't' => 'border-t-[2px]',
                'b' => 'border-b-[2px]',
                'r' => 'border-r-[2px]',
                'l' => 'border-l-[2px]',
            ],

            // "Best previously reached" shading
            'bestReachedFill' => 'bg-[#dadadf] dark:bg-[#3e3e41]',

            // Arrow overlay geometry
            'arrowTipWidthPx' => 6, // width of the pointed tip
            'arrowInnerInsetPx' => 2, // gap between outer edge and inner "centre" layer on the flat (seam) side + top/bottom
            'arrowInnerPointOffsetPx' => 3, // how close the inner layer's point comes to the very tip
            'arrowZIndex' => 10,

            // Colour palette
            // fill     segment background
            // edge_* colour suffix for the border-t-/-b-/-l-/-r- classes
            // swatch   `bg-*` colour for the arrow overlay's border layer
            // center   `bg-*` colour for the arrow overlay's inner layer
            // priority lower number wins when two adjacent segments touch (decides which colour a shared border gets)

            'palette' => [
                'default' => [
                    'fill' => 'bg-zinc-200 dark:bg-[#212124]',
                    'edge_t' => 'border-t-zinc-500 dark:border-t-[#5a5a5e]',
                    'edge_b' => 'border-b-zinc-500 dark:border-b-[#5a5a5e]',
                    'edge_r' => 'border-r-zinc-500 dark:border-r-[#5a5a5e]',
                    'edge_l' => 'border-l-zinc-500 dark:border-l-[#5a5a5e]',
                    'swatch' => 'bg-zinc-500 dark:bg-[#5a5a5e]/60',
                    'center' => 'bg-zinc-100 dark:bg-[#1c1c1f]',
                    'priority' => 4,
                ],
                'covered' => [
                    'fill' => 'bg-cyan-200 dark:bg-cyan-600/90',

                    'edge_t' => 'border-t-cyan-500 dark:border-t-cyan-400/60',
                    'edge_b' => 'border-b-cyan-500 dark:border-b-cyan-400/60',
                    'edge_r' => 'border-r-cyan-500 dark:border-r-cyan-400/60',
                    'edge_l' => 'border-l-cyan-500 dark:border-l-cyan-400/60',
                    'swatch' => 'bg-cyan-500 dark:bg-cyan-400/60',
                    'center' => 'bg-cyan-200 dark:bg-cyan-700/90',
                    'priority' => 3,
                ],
                'improve' => [
                    'fill' => 'bg-emerald-300 dark:bg-emerald-600',
                    'edge_t' => 'border-t-emerald-500 dark:border-t-emerald-400',
                    'edge_b' => 'border-b-emerald-500 dark:border-b-emerald-400',
                    'edge_r' => 'border-r-emerald-500 dark:border-r-emerald-400',
                    'edge_l' => 'border-l-emerald-500 dark:border-l-emerald-400',
                    'swatch' => 'bg-emerald-500 dark:bg-emerald-400',
                    'center' => 'bg-emerald-300 dark:bg-emerald-600',
                    'priority' => 1,
                ],
                'decline' => [
                    'fill' => 'bg-red-200 dark:bg-red-700',
                    'edge_t' => 'border-t-red-500 dark:border-t-red-500/60',
                    'edge_b' => 'border-b-red-500 dark:border-b-red-500/60',
                    'edge_r' => 'border-r-red-500 dark:border-r-red-500/60',
                    'edge_l' => 'border-l-red-500 dark:border-l-red-500/60',
                    'swatch' => 'bg-red-500 dark:bg-red-500/60',
                    'center' => 'bg-red-200 dark:bg-red-700',
                    'priority' => 2,
                ],
            ],
        ];

        $current = $getCurrentScore();
        $previous = $getPreviousScore();
        $best = $getBestScore();
        $delta = $getDelta();

        $currentVal = $current->value;
        $previousVal = $previous->value;
        $bestVal = $best->value;

        $segments = $config['segments'];
        $labels = $config['labels'];
        $palette = $config['palette'];
        $segmentCount = count($segments);
        $segWidthPercent = 100 / $segmentCount;

        /**
         * A score below 2 means it has not been covered so nothing is highlighted.
         * $hasPrevious mirrors the same check for the previous score, since
         * an unscored previous value means there's no trend to compare against.
         */
        $isValid = $currentVal >= 2;
        $hasPrevious = $previousVal >= 2;

        // If there is no valid previous score (or it is below 2), treat it as 1 to enable an improvement trend to >= 2.
        $effectivePrevious = $hasPrevious ? $previousVal : 1;
        $showBestFill = $hasPrevious && $bestVal > $currentVal;

        // Segment State
        // Decide each segment's colour token, and whether an arrow overlay is needed
        $segmentTokens = array_fill_keys($segments, 'default');
        $arrowValue = null;
        $arrowDirection = null;

        if ($isValid) {
            if ($currentVal === $effectivePrevious) {
                // No trend changes: just mark everything up to current as covered.
                foreach ($segments as $v) {
                    if ($v <= $currentVal) $segmentTokens[$v] = 'covered';
                }
            } elseif ($currentVal > $effectivePrevious) {
                // Improved: old range stays "covered", newly gained range is green.
                foreach ($segments as $v) {
                    $segmentTokens[$v] = ($v <= $effectivePrevious) ? 'covered' : (($v <= $currentVal) ? 'improve' : 'default');
                }
                $arrowValue = $currentVal;
                $arrowDirection = 'right';
            } else {
                // Declined: current range stays "covered", the lost range is highlighted red with a left-pointing arrow at the boundary.
                foreach ($segments as $v) {
                    $segmentTokens[$v] = ($v <= $currentVal) ? 'covered' : (($v <= $effectivePrevious) ? 'decline' : 'default');
                }
                $firstDeclined = $currentVal + 1;
                if (in_array($firstDeclined, $segments, true)) {
                    $arrowValue = $firstDeclined;
                    $arrowDirection = 'left';
                }
            }
        }

        // Delta Indicator: text + colour for the small "+1 / -1" shown in the header.
        $deltaColor = $delta > 0 ? $config['deltaColorPositive'] : ($delta < 0 ? $config['deltaColorNegative'] : $config['deltaColorNeutral']);
        $deltaText = $delta > 0 ? '+'.$delta : (string)$delta;
    @endphp

    <div class="{{ $config['containerClasses'] }}">
        <div class="{{ $config['headerWrapperClasses'] }}">
            <span class="{{ $config['headerLabelClasses'] }}">{{ $config['headerLabel'] }}</span>
            @if ($hasPrevious && $deltaText !== '0')
                <span class="{{ $config['deltaClasses'] }} {{ $deltaColor }}">{{ $deltaText }}</span>
            @endif
        </div>

        <div class="{{ $config['trackClasses'] }}">
            @foreach ($segments as $index => $value)
                @php
                    $token = $segmentTokens[$value];
                    $style = $palette[$token];

                    /**
                     * "Best previously reached" highlight.
                     *
                     * Shades every segment from the current value up through the best value
                     * If a value also falls within the current decline/improve/covered range, that
                     * live trend colour  takes priority over this "best reached" shade.
                     */
                    $fill = ($showBestFill && $value <= $bestVal && $token === 'default') ? $config['bestReachedFill'] : $style['fill'];

                    $isFirst = $index === 0;
                    $isLast = $index === $segmentCount - 1;
                    $roundClass = ($isFirst ? 'rounded-l-lg ' : '') . ($isLast ? 'rounded-r-lg' : '');

                    /**
                     * Shared border logic: where two segments touch, the
                     * shared border takes the colour of whichever segment
                     * has the lower `priority` value so the correct shared border colour is used
                     */
                    $rightEdgeClass = $style['edge_r'];
                    if (!$isLast) {
                        $nextStyle = $palette[$segmentTokens[$segments[$index + 1]]];
                        $rightEdgeClass = ($style['priority'] <= $nextStyle['priority']) ? $style['edge_r'] : $nextStyle['edge_r'];
                    }

                    $borderWidthClass = "{$config['segmentBorders']['t']} {$config['segmentBorders']['b']} {$config['segmentBorders']['r']}";
                    $leftBorderClass = $isFirst ? "{$config['segmentBorders']['l']} {$style['edge_l']}" : '';
                @endphp
                <div class="h-full flex-1 {{ $borderWidthClass }} {{ $style['edge_t'] }} {{ $style['edge_b'] }} {{ $rightEdgeClass }} {{ $leftBorderClass }} {{ $fill }} {{ $roundClass }}" title="{{ $labels[$value] }}"></div>
            @endforeach

            @if ($arrowValue !== null)
                @php
                    $arrowIndex = array_search($arrowValue, $segments, true);
                    $isRight = $arrowDirection === 'right';
                    $truncated = $isRight ? ($arrowIndex === $segmentCount - 1) : ($arrowIndex === 0);
                @endphp
                @if (!$truncated)
                    @php
                        $style = $palette[$segmentTokens[$arrowValue]];
                        $tipPx = $config['arrowTipWidthPx'];
                        $insetPx = $config['arrowInnerInsetPx'];
                        $pointPx = $config['arrowInnerPointOffsetPx'];

                        // Outer clip: the full chevron shape
                        $outerClip = $isRight ? "polygon(0 0, calc(100% - {$tipPx}px) 0, 100% 50%, calc(100% - {$tipPx}px) 100%, 0 100%)" : "polygon({$tipPx}px 0, 100% 0, 100% 100%, {$tipPx}px 100%, 0 50%)";

                        // Inner clip: a smaller chevron inset from the outer one, creating a visible coloured "border" ring
                        $innerClip = $isRight
                            ? "polygon(0px {$insetPx}px, calc(100% - ".($tipPx + $insetPx)."px) {$insetPx}px, calc(100% - {$pointPx}px) 50%, calc(100% - ".($tipPx + $insetPx)."px) calc(100% - {$insetPx}px), 0px calc(100% - {$insetPx}px))"
                            : "polygon(".($tipPx + $insetPx)."px {$insetPx}px, calc(100% - {$insetPx}px) {$insetPx}px, calc(100% - {$insetPx}px) calc(100% - {$insetPx}px), ".($tipPx + $insetPx)."px calc(100% - {$insetPx}px), {$pointPx}px 50%)";

                        // Horizontal position of the whole overlay
                        $leftPos = $isRight ? "calc(".($arrowIndex * $segWidthPercent)."% + {$insetPx}px)" : "calc(".($arrowIndex * $segWidthPercent)."% - {$tipPx}px)";
                    @endphp
                    <div class="absolute top-0 h-full" style="left: {{ $leftPos }}; width: calc({{ $segWidthPercent }}% + {{ $tipPx }}px); clip-path: {{ $outerClip }}; z-index: {{ $config['arrowZIndex'] }};" title="{{ $labels[$arrowValue] }}">
                        {{-- Base fill --}}
                        <div class="absolute inset-0 {{ $style['fill'] }}"></div>

                        {{-- "Border" ring, visible only in the gap between outer and inner clip shapes --}}
                        <div class="absolute inset-0 {{ $style['swatch'] }}"></div>

                        {{-- Inner "centre" fill, clipped to the smaller inset chevron --}}
                        <div class="absolute inset-0 {{ $style['center'] }}" style="clip-path: {{ $innerClip }};"></div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-dynamic-component>
