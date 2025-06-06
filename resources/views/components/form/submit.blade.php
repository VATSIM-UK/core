<div class="form-group text-center">
    <button type="submit" class="btn btn-default {{ $options['class'] ?? '' }}" @if(isset($options['id'])) id="{{ $options['id'] }}" @endif @if(isset($options['name'])) name="{{ $options['name'] }}" @endif>
        {{ $value }}
    </button>
</div>

