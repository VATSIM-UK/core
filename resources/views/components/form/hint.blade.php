<span class="help">
    @if($link)
        <a href="{{ array_get($link, 'url', '#') }}" @foreach(array_get($link, 'attributes', []) as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>{{ $text }}</a>
    @else
        {{ $text }}
    @endif
</span>

