<span class="help">
    @if($link)
        {{ link_to(array_get($link, "url", "#"), $text, array_get($link, "attributes", [])) }}
    @else
        {{ $text }}
    @endif
</span>