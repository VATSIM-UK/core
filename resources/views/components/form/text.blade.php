<div class="form-group">
    <label for="{{ $name }}" class="control-label">{{ ucfirst($name) }}</label>
    <input type="text" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}" class="form-control @if(isset($attributes['class'])) {{ $attributes['class'] }} @endif" @foreach($attributes as $attr => $val) @if($attr != 'class') {{ $attr }}="{{ $val }}" @endif @endforeach>
</div>

