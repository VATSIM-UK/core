<li class="question-item" {!! isset($hideme) ? 'style="display:none" id="question_template"' : '' !!}>
    <div class="box permanent">
        <div class="box-header">
            @if (!isset($hideme) && isset($question->id))
                {{ Form::hidden('question[template][exists]', $question->id) }}
            @endif
            <div class="input-group">
                <span class="input-group-addon"><i class="ion ion-gear-b question-settings-control"></i></span>
                <span class="input-group-addon" id="question-name-addon"><b>Question</b></span>
                {{ Form::text('question[template][name]', $question->question, ['aria-describedby' => 'question-name-addon', 'size' => 50, 'class' => 'form-control']) }}
                <span class="input-group-addon">(<span
                            class="question_type">{{ isset($question->type->name) ? trans('feedback.type.'.$question->type->name) : "" }}</span>)</span>
                <span class="input-group-addon">
                    <i class="ion ion-arrow-up-a questionButtonUp"></i>
                    <i class="ion ion-arrow-down-a questionButtonDown"></i>
                    <i class="ion ion-close question-delete-button"></i>
                </span>
            </div>
        </div>
        <div class="box-body form-horizontal" style="{{ isset($hideme) ? 'display:block;' : '' }}">
            {{ Form::hidden('question[template][type]', isset($question->type->name) ? $question->type->name : "", ['class' => 'question_type_field']) }}
            <div class="form-group">
                <label for="question[template][slug]" class="control-label col-md-2">Slug</label>
                <div class="col-md-8">
                    <input placeholder="A unique one-word identifier" class="form-control"
                           name="question[template][slug]"
                           type="text" value="{{ $question->slug }}" id="question[template][slug]">
                </div>
            </div>

            <div class="form-group">
                <label for="question[template][required]" class="control-label col-md-2">Required</label>
                <div class="col-md-8">

                    <select class="form-control" name="question[template][required]" id="question[template][required]">
                        <option value="1" {{ (!isset($question->required) || $question->required) ? 'selected' : '' }}>
                            Yes
                        </option>
                        <option value="0" {{ (!isset($question->required) || $question->required) ? '' : 'selected' }}>
                            No
                        </option>
                    </select>
                </div>
            </div>

            @if ((isset($question->type->requires_value) && $question->type->requires_value) || isset($hideme))
                <div class="form-group question_valueinput">
                    <label for="question[template][options][values]" class="control-label col-md-2">Values</label>
                    <div class="col-md-8">
                        <input placeholder="e.g Good,Average,Bad" class="form-control"
                               name="question[template][options][values]" type="text"
                               value="{{ $question->optionValues() ? join(',', $question->optionValues()) : '' }}"
                               id="question[template][options][values]">
                        <span class="help-block"><strong>Please enter in values, separated by a comma.</strong></span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</li>
