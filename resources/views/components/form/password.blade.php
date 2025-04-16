<div class="form-group">
    <label for="{{ $name }}" class="control-label">{{ $name }}</label>
    <input type="password" name="{{ $name }}" id="{{ $name }}" class="form-control" {{ $attributes }}>

    @if(count($hint) > 0)
        {{ Form::hint($hint['text'], array_get($hint, "link", [])) }}
    @endif
</div>
