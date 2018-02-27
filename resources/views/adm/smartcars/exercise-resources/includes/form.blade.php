@if(!$resource->exists)
    <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
        <label for="type">Type</label>

        <select id="type" name="type" class="form-control" onchange="$('a[href=\'#tab-'+this.value+'\']').tab('show')">
            <option value="file" {{ old('type') === 'file' ? 'selected' : ''}}>File</option>
            <option value="uri" {{ old('type') === 'uri' ? 'selected' : ''}}>URI</option>
        </select>

        @if ($errors->has('type'))
            <span class="help-block">
            <strong>{{ $errors->first('type') }}</strong>
        </span>
        @endif
    </div>
@endif

<div class="form-group">
    <label for="display-name">Display Name</label>
    <input type="text" id="display-name" name="display_name" class="form-control"
           value="{{ old('display_name') ?: $resource->display_name }}" placeholder="Resource Name" required>
</div>

<a href="#tab-file" class="hidden" role="tab" data-toggle="tab">File</a>
<a href="#tab-uri" class="hidden" role="tab" data-toggle="tab">URI</a>
<div class="tab-content">
    @if(!$resource->exists || $resource->type === 'file')
        <div id="tab-file" class="tab-pane {{ is_null(old('type')) || old('type') === 'file' ? 'in active' : '' }}">
            <div class="form-group">
                <label for="file">File</label>
                <input type="file" id="file" name="file" class="form-control">
            </div>
        </div>
    @endif

    @if(!$resource->exists || $resource->type === 'uri')
        <div id="tab-uri" class="tab-pane {{ old('type') === 'uri' || $resource->type === 'uri' ? 'in active' : '' }}">
            <div class="form-group">
                <label for="uri">URI</label>
                <input type="url" id="uri" name="uri" class="form-control"
                       value="{{ old('uri') ?: ($resource->type === 'uri' ? $resource->resource : '') }}"
                       placeholder="https://example.com/link-to-resource">
            </div>
        </div>
    @endif
</div>

<input class="btn btn-primary" type="submit" value="Submit">
<a class="btn btn-default" href="{{ route('adm.smartcars.exercises.resources.index', $flight) }}">Cancel</a>
