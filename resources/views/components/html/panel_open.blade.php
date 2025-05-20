@props([
    'attr' => []
])
<div class="panel panel-ukblue">
    <div class="panel-heading">
        @if($icon)
            <i class="{{ $icon['type'] ?? '' }} {{ $icon['key'] ?? '' }}"></i>&thinsp;
        @endif
        {{ $title }}
    </div>
    <div class="panel-body" @foreach($attr as $k => $v) {!! $k.'="'.$v.'" ' !!} @endforeach>

