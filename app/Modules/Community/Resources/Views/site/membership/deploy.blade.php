@extends('visittransfer::site._layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("What is this?", ["type" => "vuk", "key" => "letter-w"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <p>
                        {!! trans("community::membership.info") !!}
                    </p>
                </div>
            </div>
            {!! HTML::panelClose() !!}
        </div>

        <div class="col-md-6">
            {!! HTML::panelOpen("Transfer Group", ["type" => "vuk", "key" => "letter-t"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    {!! Form::open(["route" => ["community.membership.deploy.post"], "method" => "POST"]) !!}
                    <p>
                        {!! trans("community::membership.transfer.info") !!}
                    </p>

                    {!! Form::close() !!}
                </div>
            </div>

            {!! HTML::panelClose() !!}
        </div>

        <div class="col-md-6">
            {!! HTML::panelOpen("Join Group", ["type" => "vuk", "key" => "letter-j"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    {!! Form::open(["route" => ["community.membership.deploy.post"], "method" => "POST"]) !!}
                    <p>
                        {!! trans("community::membership.deploy.info") !!}
                    </p>

                    <div class="col-md-6">
                        {!! ControlGroup::generate(
                            Form::label("groups","Group"),
                            Form::select("groups", [
                                "Please select a group..." => $groups->pluck("name", "id")->toArray()
                            ])
                        )->withAttributes(["id" => "refereePositionHelp"]) !!}
                    </div>

                    <div class="text-center" style="padding-top: 27px;">
                        {!! Button::primary("JOIN UK COMMUNITY GROUP")->submit() !!}
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>

            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop