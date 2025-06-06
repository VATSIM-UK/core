<div class="form-group">
    <label for="{{ $name }}" class="control-label">{{ ucfirst($name) }}</label>
    <input type="password" name="{{ $name }}" id="{{ $name }}" class="form-control @if(isset($attributes['class'])) {{ $attributes['class'] }} @endif" @foreach($attributes as $attr => $val) @if($attr != 'class') {{ $attr }}="{{ $val }}" @endif @endforeach>

    @if(count($hint) > 0)
        <small class="form-text text-muted">
            {{ $hint['text'] }}
            @if(isset($hint['link']))
                <a href="{{ $hint['link'] }}" target="_blank">More info</a>
            @endif
        </small>
    @endif
</div>

