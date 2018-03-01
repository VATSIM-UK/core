<li class="question-item" {{ isset($hideme) ? "style=display:none id=question_template" : "" }}>
  <div class="col-md-12 permanent">
    <div class="box" style="border: 1px solid;background-color:#4f798c;color:white">
      <div class="box-header">
        <div class="row">
          <div class="col-md-8">
            <i onclick="$('#question_control_box_{{$num}}').slideToggle()" class="ion ion-levels" style="font-size:30px; color:white; cursor:pointer; float:left; margin-right: 10px;" data-toggle="dropdown" aria-expanded="false"></i>
            <div class="input-group">
              <span class="input-group-addon" id="question-name-addon"><b>Question</b></span>
              {{ Form::text('question['.$num.'][name]', $question->question, ['aria-describedby' => 'question-name-addon', 'size' => 50]) }}
              <span class="input-group-addon">({{ isset($question->type->name) ? $question->type->name : "" }})</span>
            </div>


            @if (!isset($hideme) && isset($question->id))
              {{ Form::hidden('question['.$num.'][exists]', $question->id) }}
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
      <div class="box-body" style="display:none; background-color:#375665;" id="question_control_box_{{$num}}">
        {{ Form::hidden('question['.$num.'][type]', (isset($question->type->name) ? $question->type->name : ""), ['class' => 'question_type_field']) }}
        <table style="width:100%">
          <tr>
            <th>{{ Form::label('question['.$num.'][slug]', "Slug") }}</th>
            <td>
              {{ Form::text('question['.$num.'][slug]', $question->slug, ['placeholder' => 'A unique one-word identifier']) }}
            </td>
          </tr>
          <tr>
            <th>{{ Form::label('question['.$num.'][required]', "Required") }}</th>
            <td>
              @if ((isset($question->required) && !$question->required))
                {{ Form::select('question['.$num.'][required]', array(1 => 'Yes', 0 => 'No'), 0) }}
              @else
                {{ Form::select('question['.$num.'][required]', array(1 => 'Yes', 0 => 'No'), 1) }}
              @endif
            </td>
          </tr>
          @if ((isset($question->type->requires_value) && $question->type->requires_value) || isset($hideme))
            <tr>
              <th>{{ Form::label('question['.$num.'][options][values]', "Values") }}</th>
              <td>
                {{ Form::text('question['.$num.'][options][values]', (($question->optionValues()) ? join(",", $question->optionValues()) : ""), ['placeholder' => 'e.g Good,Average,Bad']) }}
                <small>Please enter in values, seperated by a comma</small>
              </td>
            </tr>
          @endif
        </table>
      </div>
    </div>
  </div>
</li>
