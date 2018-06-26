@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("What is this?", ["type" => "vuk", "key" => "letter-w"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1" style="margin-bottom: 15px;">
                    <p>
                        {!! trans("membership.info") !!}
                    </p>
                </div>

                <div class="col-md-5 col-md-offset-1">
                    <iframe src="https://www.google.com/maps/d/u/3/embed?mid=1Bcf58mYzCfZ4bPY9BV6fK1ASAKA&ll=53.90443554786907%2C-2.8056335343749197&z=6"
                            width="100%" height="660px"></iframe>
                </div>

                <div class="col-md-5" style="text-align: center;">
                    {!! Form::open(["route" => ["community.membership.deploy.post"], "method" => "POST"]) !!}
                    <p>
                        {!! trans("membership.deploy.info") !!}
                    </p>

                    <div class="col-md-12">
                        <div class="form-group" id="refereePositionHelp">
                            {!! Form::label("group","Group", ['class' => 'control-label']),
                                Form::select("group", ["x" => "Please select a group..."] + $groups->pluck("name", "id")->toArray(), 'x', ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="text-center" style="padding-top: 27px;">
                        <button type="submit" class="btn btn-danger" id="confirm" disabled="disabled">INVALID GROUP SELECTED</button>
                    </div>

                    {!! Form::close() !!}

                    <hr />


                    <p>
                        {!! trans("membership.deploy.uk.info") !!}
                    </p>
                        {!! Form::open(["route" => ["community.membership.deploy.post"], "method" => "POST"]) !!}
                        {!! Form::hidden("group", $defaultGroup->id) !!}

                        <div class="text-center" style="padding-top: 27px;">
                            <button type="submit" class="btn btn-danger" id="ukonly">
                                I do not wish to join any region-based community group at this time.
                            </button>
                        </div>

                        {!! Form::close() !!}
                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop

@section("scripts")
    @parent

    <script type="text/javascript" language="javascript">
        var BUTTON_SELECTOR = '#confirm';
        var BUTTON_TEXT_INVALID = '{!! trans("membership.deploy.button.invalid") !!}';
        var BUTTON_TEXT_VALID = '{!! trans("membership.deploy.button.valid") !!}';
        var DROPDOWN_SELECTOR = '#group';

        function confirmValidGroupSelection() {
            var chosenGroup = $('#groups option:selected');

            if(chosenGroup.val() == "x"){
                $(BUTTON_SELECTOR).prop('disabled', true);
                $(BUTTON_SELECTOR).removeClass('btn-primary');
                $(BUTTON_SELECTOR).addClass('btn-danger');
                $(BUTTON_SELECTOR).text(BUTTON_TEXT_INVALID);
            } else {
                $(BUTTON_SELECTOR).prop('disabled', false);
                $(BUTTON_SELECTOR).addClass('btn-primary');
                $(BUTTON_SELECTOR).removeClass('btn-danger');
                $(BUTTON_SELECTOR).text(BUTTON_TEXT_VALID.replace(/%s/g, chosenGroup.text()));
            }
        }

        $(DROPDOWN_SELECTOR).change(confirmValidGroupSelection);

        window.onload = confirmValidGroupSelection;
    </script>

@stop