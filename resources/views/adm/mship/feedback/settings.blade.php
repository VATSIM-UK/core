@extends('adm.layout')

@section('scripts')
  {!! HTML::script(asset("assets/js/plugins/datetimepickerxdan/jquery.datetimepicker.full.min.js")) !!}
  {!! HTML::script(asset('assets/js/plugins/jquerysortable/jquery-sortable.js')) !!}
  <script type="text/javascript">
    $(function  () {
      var count = $("#feedback-form-questions li").length;

      // Make the question list sortable & droppable
      $("ol#feedback-form-questions").sortable({
          group: 'no-simple_connected_list',
          handle: '.box-header',
          drag: false,
          onDragStart: function ($item, container, _super) {
            // Duplicate items of the no drop area
            if(!container.options.drop)
              $item.clone().insertAfter($item);
            _super($item, container);
          },
          onDrop: function ($item, container, _super, event){
              if(!$item.find("div").first().hasClass('permanent')){
                count = count + 1;
                var itemtype = $item.find(".type_name").first().text();
                var needsvalue = $item.find("div").first().hasClass("needs-values");
                // Duplicate the question template
                $item.html($("#question_template").html());
                $item.find(".question_type").first().text(itemtype);
                $item.find(".question_type_field").first().val(itemtype);
                if(!needsvalue){
                  $item.find(".question_valueinput").first().hide();
                }
                //$item.html($item.html().replace(/template/g, count));
              }
              $item.removeClass(container.group.options.draggedClass).removeAttr("style")
              $item.addClass("question-item")
              $("body").removeClass(container.group.options.bodyClass)
          }
      });

      // Make the question types dragable
      $("ol#question-types-box").sortable({
        drop: false,
        group: 'no-simple_connected_list'
      });

      // Send the old question layout with form submittion, so that it is easier if something goes wrong
      $("#form-questions-form").submit(function (event){
          // Quickly number the arrays
          var count = 1;
          $("#feedback-form-questions").children(".question-item").each(function (){
            console.log(count)
            console.log(this)
            $(this).html($(this).html().replace(/template/g, count))
            count = count + 1;
          })
          $('#old_data_input').val($('#feedback-form-questions').html())
          //event.preventDefault()
      });

      // Detect change in input values so that they are preserved if form submission fails
      $("#feedback-form-questions").on("change keyup paste", "input", function() {
        $(this).attr('value', $(this).val());
      });
      $("#feedback-form-questions").on("change keyup paste", "select", function() {
        $(this).children().attr('selected', "");
        if($(this).val() == "0"){
          $(this).children().first().removeAttr('selected')
        }else{
          $(this).children().eq(1).removeAttr('selected')
        }
      });

      // Add in javascript question controls
      $("#feedback-form-questions").on("click", ".questionButtonUp", function() {
          $(this).parents(".question-item").insertBefore($(this).parents(".question-item").prev());
      });
      $("#feedback-form-questions").on("click", ".questionButtonDown", function() {
          $(this).parents(".question-item").insertAfter($(this).parents(".question-item").next());
      });
      $("#feedback-form-questions").on("click", ".question-delete-button", function() {
          $(this).closest('.question-item').remove();
      });
    });
    $(document).ready(function(){
      $('.datetimepickercustom').datetimepicker();
    });
  </script>
@endsection

@section('content')
<!-- Main row -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Customize the Feedback Form</br>
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    {!! Form::open(["id" => "form-questions-form","route" => ["adm.mship.feedback.config.save", $form->id]]) !!}
                    {{ Form::hidden("old_data", "", ['id' => 'old_data_input'])}}
                    <div class="col-md-9">
                      <div class="box box-primary">
                          <div class="box-header">
                            <h4 class="box-title" style="font-size:1.5em">
                                Form Questions
                            </h4>
                          </div>
                          <div class="box-body">
                              <div class="row">
                                <ol class='simple_connected_list' id="feedback-form-questions">
                                  @if (old('old_data') != null)
                                    {!! old('old_data') !!}
                                  @else
                                    @php
                                      $i = 1;
                                    @endphp
                                      @foreach ($current_questions as $question)
                                        @include('adm.mship.feedback._question', ['question' => $question, 'num' => 'template'])
                                        @php
                                          $i++;
                                        @endphp
                                      @endforeach


                                  @endif
                                </ol>
                              </div>
                          </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="row">
                          <div class="col-md-6">
                              <div class="box box-danger">
                                  <div class="box-header">

                                  </div>
                                  <div class="box-body">
                                    {{ Form::submit("Save Changes", ['class' => 'btn btn-success', 'style' => 'color:white;']) }}
                                  </div>
                              </div>
                          </div>
                          {{ Form::close() }}
                          @include('adm.mship.feedback._question', ['question' => $new_question, 'hideme' => true, 'num' => 'template'])
                          <div class="col-md-12">
                              <div class="box box-warning">
                                <div class="box-header">
                                  <h4 class="box-title" style="font-size:1.5em">
                                      Input Types</br>
                                      <small>Click and drag these types across</small>
                                  </h4>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                      <ol class='simple_connected_list' id="question-types-box">
                                          @foreach ($question_types as $type)
                                            @include('adm.mship.feedback._type', ['type' => $type])
                                          @endforeach
                                      </ol>
                                    </div>
                                </div>
                            </div>
                          </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.row (main row) -->
@stop
