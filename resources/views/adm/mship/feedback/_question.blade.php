<li class="question-item" {{ isset($hideme) ? "style=display:none id=question_template" : "" }}>
  <div class="col-md-12 permanent">
    <div class="box">
      <div class="box-header">
        <div class="row">
          <div class="col-md-8">
            <i class="ion ion-gear-b question-settings-control"></i>
            <div class="input-group">
              <span class="input-group-addon" id="question-name-addon"><b>Question</b></span>
              {{ Form::text('question[template][name]', $question->question, ['aria-describedby' => 'question-name-addon', 'size' => 50]) }}
              <span class="input-group-addon">(<span class="question_type">{{ isset($question->type->name) ? $question->type->name : "" }}</span>)</span>
            </div>
            @if (!isset($hideme) && isset($question->id))
              {{ Form::hidden('question[template][exists]', $question->id) }}
            @endif
          </div>
          <div class="col-md-4 text-center">
            <div class="btn-group" role="group">
              <button type=button class="btn btn-xs btn-danger question-delete-button">Delete</button>
              <button type="button" class="btn btn-xs questionButtonUp">Move Up</button>
              <button type="button" class="btn btn-xs questionButtonDown">Move Down</button>
            </div>
          </div>
        </div>
      </div>
      <div class="box-body" style="{{ isset($hideme) ? 'display:block' : '' }}">
        {{ Form::hidden('question[template][type]', (isset($question->type->name) ? $question->type->name : ""), ['class' => 'question_type_field']) }}
        <table style="width:100%">
          <tr>
            <th>{{ Form::label('question[template][slug]', "Slug") }}</th>
            <td>
              {{ Form::text('question[template][slug]', $question->slug, ['placeholder' => 'A unique one-word identifier']) }}
            </td>
          </tr>
          <tr>
            <th>{{ Form::label('question[template][required]', "Required") }}</th>
            <td>
              @if ((isset($question->required) && !$question->required))
                {{ Form::select('question[template][required]', array(1 => 'Yes', 0 => 'No'), 0) }}
              @else
                {{ Form::select('question[template][required]', array(1 => 'Yes', 0 => 'No'), 1) }}
              @endif
            </td>
          </tr>
          @if ((isset($question->type->requires_value) && $question->type->requires_value) || isset($hideme))
            <tr class="question_valueinput">
              <th>{{ Form::label('question[template][options][values]', "Values") }}</th>
              <td>
                {{ Form::text('question[template][options][values]', (($question->optionValues()) ? join(",", $question->optionValues()) : ""), ['placeholder' => 'e.g Good,Average,Bad']) }}
                <small>Please enter in values, seperated by a comma</small>
              </td>
            </tr>
          @endif
        </table>
      </div>
    </div>
  </div>
</li>
