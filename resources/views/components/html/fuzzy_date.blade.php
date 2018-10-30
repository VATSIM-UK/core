@if($timestamp)
    <span class="fuzzy_date tooltip_displays" data-toggle="tooltip" title="{{ $timestamp }}">
        {{ $timestamp->diffForHumans() }}
    </span>
@endif