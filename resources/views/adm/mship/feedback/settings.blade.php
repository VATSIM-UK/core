@extends('adm.layout')

@section('content')
    <div class="row">
        {!! Form::open(["id" => "form-questions-form", "route" => ["adm.mship.feedback.config.save", $form->slug]]) !!}
        {{ Form::hidden("old_data", "", ['id' => 'old_data_input'])}}
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header">
                    <h4 class="box-title" style="font-size:1.5em">
                        Form Questions
                    </h4>
                    <br>
                    <small><strong>Note:</strong>
                        You do NOT need to add a 'userlookup' question if the form is targeted. This is added
                        automatically.
                    </small>
                </div>
                <div class="box-body feedback-form-config">
                    @if ($form->targeted)
                        <div class="permanent question-item">
                            <div class="input-group">
                                <span class="input-group-addon" id="question-name-addon"><b>Question</b></span>
                                {{ Form::text('', 'CID of the member you are leaving feedback for.', ['class' => 'form-control', 'aria-describedby' => 'question-name-addon', 'size' => 50, 'disabled']) }}
                                <span class="input-group-addon">(Permanent)</span>
                            </div>
                        </div>
                    @endif
                    <ol class="simple_connected_list" id="feedback-form-questions">
                        @if (old('old_data') != null)
                            {!! old('old_data') !!}
                        @else
                            @for ($i = 0; $i < $current_questions->count(); $i++)
                                @include('adm.mship.feedback._question', ['question' => $current_questions[$i], 'num' => $i+1])
                            @endfor
                        @endif
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-danger">
                <div class="box-header">
                    <h4 class="box-title">Form Controls</h4>
                </div>
                <div class="box-body text-center">
                    {{ Form::submit("Save Changes", ['class' => 'btn btn-success', 'style' => 'color:white;']) }}
                    {{ Form::close() }}<br><br>
                    <div class="btn-group">
                        <a class="btn{{ $form->enabled ? ' btn-danger' : ' btn-success' }}" style="color:white;"
                           href="{{route("adm.mship.feedback.config.toggle", $form)}}">{{ $form->enabled ? 'Disable' : 'Enable' }}
                            Form</a>
                        <a class="btn{{ $form->public ? ' btn-danger' : ' btn-success' }}" style="color:white;"
                           href="{{route("adm.mship.feedback.config.visibility", $form)}}">Make {{ $form->public ? 'Unlisted' : 'Listed' }}</a>
                    </div>
                    <div style="word-break: break-word;">
                        Form Link:
                        {{link_to_route('mship.feedback.new.form', route('mship.feedback.new.form', $form), $form->slug)}}
                    </div>
                </div>
            </div>
            @include('adm.mship.feedback._types')
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sortable/0.9.13/jquery-sortable-min.js"
            integrity="sha384-mwD0+87SDVjJjyfTMQHNVV+IyWDM38MhzdCFZ+SRefmD75v+M5K0R3naFNLnZf1L"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"
            integrity="sha384-8Lb23xW0dVl+HHrv90JF6PpwygXa7Z0zZIK9+RWorNDyubrG7Ppu7JJw32U8op0i"
            crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(function () {
            var count = $("#feedback-form-questions li").length;

            // Make the question list sortable & droppable
            $("ol#feedback-form-questions").sortable({
                group: 'no-simple_connected_list',
                handle: '.box-header',
                drag: false,
                onDragStart: function ($item, container, _super) {
                    // Duplicate items of the no drop area
                    if (!container.options.drop)
                        $item.clone().insertAfter($item);
                    _super($item, container);
                },
                onDrop: function ($item, container, _super, event) {
                    if (!$item.find("div").first().hasClass('permanent')) {
                        count = count + 1;
                        var itemtype = $item.find(".type_name").first().text();
                        var needsvalue = $item.find("div").first().hasClass("needs-values");
                        // Duplicate the question template
                        $item.html($("#question_template").html());
                        $item.find(".question_type").first().text(itemtype);
                        $item.find(".question_type_field").first().val(itemtype);
                        if (!needsvalue) {
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
            $("#form-questions-form").submit(function (event) {
                // Quickly number the arrays
                var count = 1;
                $("#feedback-form-questions").children(".question-item").each(function () {
                    $(this).html($(this).html().replace(/template/g, count))
                    count = count + 1;
                })
                $('#old_data_input').val($('#feedback-form-questions').html())

            });

            // Detect change in input values so that they are preserved if form submission fails
            $("#feedback-form-questions").on("change keyup paste", "input", function () {
                $(this).attr('value', $(this).val());
            });
            $("#feedback-form-questions").on("change keyup paste", "select", function () {
                $(this).children().attr('selected', "");
                if ($(this).val() == "0") {
                    $(this).children().first().removeAttr('selected')
                } else {
                    $(this).children().eq(1).removeAttr('selected')
                }
            });

            // Add in javascript question controls
            $("#feedback-form-questions").on("click", ".questionButtonUp", function () {
                $(this).parents(".question-item").insertBefore($(this).parents(".question-item").prev());
            });
            $("#feedback-form-questions").on("click", ".questionButtonDown", function () {
                $(this).parents(".question-item").insertAfter($(this).parents(".question-item").next());
            });
            $("#feedback-form-questions").on("click", ".question-delete-button", function () {
                $(this).closest('.question-item').remove();
            });

            // Question accordion control
            $("#feedback-form-questions").on('click', '.question-settings-control', function () {
                $(this).closest('.box').children('.box-body').slideToggle()
            });
        });
        $(document).ready(function () {
            $('.datetimepickercustom').datetimepicker();
        });
    </script>
@endsection
