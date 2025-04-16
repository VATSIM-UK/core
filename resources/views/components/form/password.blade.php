<div class="form-group">
    {{ Form::label($name, null, ['class' => 'control-label']) }}
    {{ Form::password($name, array_merge_concat(['class' => 'form-control'], $attributes)) }}

    @if(count($hint) > 0)
        {{ Form::hint($hint['text'], array_get($hint, "link", [])) }}
    @endif
</div>