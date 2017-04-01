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
			Here you can submit feedback about a UK division member. Please try to explain your answers fully. Abuse of this system will not be tolerated.
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
            {{ Form::label($question->slug, $question->question . ($question->required ? "" : " (optional)")) }}
            @if ($question->type == "text")
              {{ Form::text($question->slug) }}
            @elseif ($question->type == "radio" && isset($question->options['values']))
              </br>
              @foreach ($question->options['values'] as $key => $value)
                {{ Form::radio($question->slug, $value) }} {{$key}}
              @endforeach
            @elseif ($question->type == "textarea")
              {{ Form::textarea($question->slug) }}
            @elseif ($question->type == "datetime")
              {{ Form::text($question->slug, "03/09/2017", ['class' => "datetimepickercustom"]) }}
            @elseif ($question->type == "userlookup")
              {{ Form::text($question->slug, "", ['placeholder' => "Enter the Users CID e.g 1234567"]) }}
            @endif
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
