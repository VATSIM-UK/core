<div class="progress" data-toggle="tooltip" title="{{ $text }}">
    <div class="progress-bar {{$class}} {{$complete() ? ' progress-bar-success': null}}"
         role="progressbar"
         style="width: {{ $cappedPercentage() }}%"
         aria-valuemin="{{$min}}"
         aria-valuemax="{{ $max }}">{{$text}}
    </div>
</div>
