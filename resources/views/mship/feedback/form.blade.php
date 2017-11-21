@extends('layout')

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js" integrity="sha384-8Lb23xW0dVl+HHrv90JF6PpwygXa7Z0zZIK9+RWorNDyubrG7Ppu7JJw32U8op0i" crossorigin="anonymous"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.datetimepickercustom').datetimepicker();

    // Member search hook

    var searchInput = $('#member-search-name');
    var timeout;

    // Bind input change for serch input
    searchInput.bind("input", function(event){

        $('#memberSearchSpinner').show();
        $('#memberSearchNoResults').hide();
        $('#memberSearchResults').hide();

        clearTimeout(timeout);
        timeout = window.setTimeout(function(){
          $.get('{{route('mship.feedback.usersearch', null)}}/' + $('#member-search-name').val(), function(response){
              $('#memberSearchSpinner').hide();
              if(response == ""){
                $('#memberSearchNoResults').show();
                $('#memberSearchResults').html('<a href="https://stats.vatsim.net/search_name.php?name='+searchInput.val()+'" target="_blank">Search VATSIM Statistics for user</a>');
                $('#memberSearchResults').show();
              }else{
                var htmlString;
                htmlString = "<div class='row'>";
                  htmlString += "<div class='col-md-12'><ul class='nav nav-pills nav-stacked'>";
                  response.forEach( function(member){
                    htmlString += "<li><a href='#' class='memberSearchMemberItem' data-cid='"+member.cid+"'><b>"+member.name+" ("+member.cid+")</b> - "+member.status+"</a></li>";
                  });
                  htmlString += "</ul></div>";
                htmlString += "</div>";
                $('#memberSearchResults').html(htmlString);
                $('#memberSearchResults').show();
              }

              $('.memberSearchMemberItem').click(function(){
                console.log("Click")
                if(formUserSelectorInput){
                  formUserSelectorInput.val($(this).data("cid"));
                }
                $('#memberSearchDialog').modal('toggle');
              });
          });
        }, 2000);
       }
     );

    var formUserSelectorInput;
    if($('#formUserLookupFieldContainer').length){
      formUserSelectorInput = $('#formUserLookupFieldContainer').find('input');
    }
  });
</script>
@endsection

@section('content')
  <!-- Modal -->
<div id="memberSearchDialog" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content panel-ukblue">
      <div class="modal-header panel-heading">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Search for a member</h4>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
              {{ Form::label('name', 'Member\'s Name') }}
              {{ Form::text('name', null, ['id' => 'member-search-name']) }}
            </div>
        </div>
        <hr>
        <div class="row" style="margin-top:20px">
            <div class="col-md-12 text-center">
              <p id="memberSearchSpinner" style="display:none">
                  <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>
              </p>
              <p id="memberSearchNoResults" style="display:none">
                  No Results Found. Try looking here:
              </p>
              <p id="memberSearchResults" style="display:none">
              </p>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div class="panel panel-ukblue">
	<div class="panel-heading"> Submit Member Feedback</div>
	<div class="panel-body">
		<!-- Content Of Panel [START] -->
		<!-- Top Row [START] -->
		<div class="row">

			<div class="col-md-7 col-md-offset-2">
        @if (!isset($form))
          {!! Form::open(["route" => ["mship.feedback.new"]]) !!}
            {{Form::label('feedback_type', 'What kind of feedback would you like to leave?')}}
            {{Form::select('feedback_type', $feedbackForms)}}
            {{Form::submit("Next")}}
          {!! Form::close() !!}
        @else
          {!! Form::open(["route" => ["mship.feedback.new.form.post", $form->slug]]) !!}
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
              @if ($question->type->name == "userlookup")
                <span id="formUserLookupFieldContainer">
                  <div class="input-group">
                    <span class="input-group-btn"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#memberSearchDialog">Search <i class="fa fa-search"></i></button></span>
                    {!! $question->form_html !!}
                  </div>
                </span>
              @else
                {!! $question->form_html !!}
              @endif

            </div>
  				@endforeach
          <div class="form-group">
            {{ Form::submit() }}
          </div>
          {!! Form::close() !!}
        @endif
			</div>
		</div>
		<!-- Second Row [END] -->
		<!-- Content Of Panel [END] -->

	</div>
</div>

@stop
