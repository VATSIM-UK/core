<div>
    @if ($value)
    {!! HTML::img("tick_mark_circle", "png", 20) !!}
    @else
    {!! HTML::img("cross_mark_circle", "png", 20) !!}
    @endif
</div>