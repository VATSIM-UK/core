<li class="question-item" {{ isset($hideme) ? "style=display:none id=question_template" : "" }}>
  <div class="col-md-12 permanent">
      <div class="box box-warning" style="border: 1px solid">
          <div class="box-header">
              <div class="row">
                  <div class="col-md-10">
                      <h4 class="box-title" style="font-size:1.2em;width:100%">
                        Question:
                        {{ Form::text('question['.$num.'][name]', $question->question) }}
                        Short Name: <small> A one-word identifier for this question. (Please ensure it is unique!) </small>
                        {{ Form::text('question['.$num.'][slug]', $question->slug) }}
                        @if (!isset($hideme) && isset($question->id))
                          {{ Form::hidden('question['.$num.'][exists]', $question->id) }}
                        @endif
                      </h4>
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-xs questionButtonUp">Up</button>
                    <button type="button" class="btn btn-xs questionButtonDown">Down</button>
                  </div>
              </div>
          </div>
          <div class="box-body">
              Question Type: <span class="question_type">{{ isset($question->type->name) ? $question->type->name : "" }}</span>
              {{ Form::hidden('question['.$num.'][type]', (isset($question->type->name) ? $question->type->name : ""), ['class' => 'question_type_field']) }}
              <hr>
              <p>
                Options:
              </p>
              <div class="form-group">
                {{ Form::label('question['.$num.'][required]', "Required") }}
                @if ((isset($question->required) && $question->required == false))
                  {{ Form::select('question['.$num.'][required]', array(1 => 'Yes', 0 => 'No'), 0) }}
                @else
                  {{ Form::select('question['.$num.'][required]', array(1 => 'Yes', 0 => 'No')) }}
                @endif
              </div>
              @if ((isset($question->type->requires_value) && $question->type->requires_value == true) || isset($hideme))
                <div class="form-group question_valueinput">
                  {{ Form::label('question['.$num.'][options][values]', "Values") }}
                  <small>Please enter in values, seperated by a comma, for the options</small>
                  {{ Form::text('question['.$num.'][options][values]', (($question->optionValues()) ? join(",", $question->optionValues()) : "")) }}
                </div>
              @endif
              <hr>
              <p>
                Preview:
              </p>
              <p>
                @if (!isset($hideme))
                  {{ Form::label($question->question) }}</br>
                  @if (isset($question->options['values']) && $question->options['values'] != "")
                      @foreach ($question->options['values'] as $value)
                          {!! sprintf($question->type->code, "", "", $value, $value, "") !!}
                      @endforeach
                  @else
                      {!! sprintf($question->type->code, "", "", "example", "example") !!}
                  @endif

                @else
                  Preview for this question will be unavalible until you save and reload this page
                @endif
              </p>
              <hr>
              <button type=button class="question-delete-button btn btn-danger">Delete</button>
          </div>
      </div>
  </div>
</li>
