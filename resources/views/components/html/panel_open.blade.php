<div class="panel panel-ukblue">
    <div class="panel-heading">
        @if($icon)
            {{ HTML::icon(array_get($icon, "type", ""), array_get($icon, "key", "")) }}
            &thinsp;
        @endif
        {{ $title }}
    </div>
    <div class="panel-body" @foreach($attr as $k => $v) {!! $k.'="'.$v.'" ' !!} @endforeach>