@extends('layout')

@section('styles')
{!! HTML::style(asset("assets\js\plugins\datetimepickerxdan\jquery.datetimepicker.min.css")) !!}
@endsection

@section('scripts')
{!! HTML::script(asset("assets\js\plugins\datetimepickerxdan\jquery.datetimepicker.full.min.js")) !!}
<script type="text/javascript">
  $(document).ready(function(){
    $('.datetimepickercustom').datetimepicker();
  });
</script>
@endsection

@section('content')
<div class="panel panel-ukblue">
	<div class="panel-heading"> Submit Member Feedback</div>
	<div class="panel-body">
		<!-- Content Of Panel [START] -->
		<p>
			Here you can submit anonymous feedback about a <b>UK</b> division member. Please try to explain your answers fully. Your identity is kept anonymous to staff &amp; the subject of the feedback. However, senior staff will be able to discover your identity in the case of abuse of this system.
		</p>
    <p>
			The contents of your responses will be sent to the relevant  training team(s). It will not necessarily be sent directly to the subject of your feedback.
		</p>
    <p>
      All questions are required unless an <i>(optional)</i> is displayed beside it.
    </p>
		<!-- Top Row [START] -->
		<div class="row">

			<div class="col-md-7 col-md-offset-2">
        {!! Form::open(["route" => ["mship.feedback.new.post"]]) !!}
				@foreach ($questions as $question)
          <div class="form-group{{ $errors->has($question->slug) ? " has-error" : "" }}">
            {{ Form::label($question->slug, $question->question . ($question->required ? "" : " (optional)")) }} </br>
            @php
                if($question->type->requires_value == true){
                  if(isset($question->options['values'])){
                    foreach($question->options['values'] as $key => $value){
                      printf($question->type->code, $question->slug, old($question->slug), $value, $value);
                    }
                    echo "</div>";
                    continue;
                  }
                  // No values, so we cant use it :/
                  echo "</div>";
                  continue;
                }

                printf($question->type->code, $question->slug, old($question->slug));
            @endphp
          </div>
				@endforeach
        <div class="form-group">
          {{ Form::submit() }}
        </div>
        {!! Form::close() !!}
			</div>
		</div>
		<!-- Second Row [END] -->
		<!-- Content Of Panel [END] -->

	</div>
</div>

@stop
