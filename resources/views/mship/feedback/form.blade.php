@extends('layout')

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js" integrity="sha384-8Lb23xW0dVl+HHrv90JF6PpwygXa7Z0zZIK9+RWorNDyubrG7Ppu7JJw32U8op0i" crossorigin="anonymous"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.datetimepickercustom').datetimepicker();
  });
</script>
@endsection

@section('content')
<div class="panel panel-ukblue">
	<div class="panel-heading">Submit Feedback</div>
	<div class="panel-body">
		<!-- Content Of Panel [START] -->
		<!-- Top Row [START] -->
		<div class="row">

			<div class="col-md-7 col-md-offset-2">
        @if (!isset($form))
                    <form method="POST" action="{{ route('mship.feedback.new') }}">
                        @csrf
            <p>
                {{Form::label('feedback_type', 'What kind of feedback would you like to leave?')}}
                {{Form::select('feedback_type', $feedbackForms, [], ['class' => 'form-control']) }}
            </p>
            <p class="text-center">
                <button type="submit" class="btn btn-primary">Next <i class="fa fa-arrow-right"></i></button>
            </p>
                    </form>
        @else
                    <form method="POST" action="{{ route('mship.feedback.new.form.post', $form) }}" autocomplete="off">
                        @csrf
        <p>
        @if($form->targeted)
          Here you can submit anonymous feedback about a <b>UK</b> division member.
          Please try to explain your answers fully. Your identity is kept anonymous to staff &amp; the subject of the feedback. However, senior staff will be able to discover your identity in the case of abuse of this system.
        @else
          Here you can submit anonymous feedback.
          Please try to explain your answers fully. Your identity is kept anonymous to staff, however senior staff will be able to discover your identity in the case of abuse of this system.
        @endif
        </p>
        <p>
          The contents of your responses will be sent to the relevant team(s).
        @if($form->targeted)
          It will not necessarily be sent directly to the subject of your feedback.
        @endif
        </p>
        <p>
          All questions are required unless an <i>(optional)</i> is displayed beside it.
        </p>
        <hr>
        @foreach ($questions as $question)
            <div class="form-group{{ $errors->has($question->slug) ? " has-error" : "" }}">
              {{ Form::label($question->slug, $question->question . ($question->required ? "" : " (optional)")) }} </br>
              {!! $question->form_html !!}
            </div>
        @endforeach
          <div class="form-group">
            <button type="submit" class="btn btn-success">Submit</button>
          </div>
                    </form>
        @endif
			</div>
		</div>
		<!-- Second Row [END] -->
		<!-- Content Of Panel [END] -->

	</div>
</div>

@stop
