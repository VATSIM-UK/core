@if($timestamp)
    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $timestamp }}">
        {{ $timestamp->diffForHumans() }}
    </a>
@endif