@extends('visittransfer::application._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("References &amp; Referees", ["type" => "fa", "key" => "comment-o"]) !!}
            <div class="row">

                <div class="col-md-6 col-md-offset-3">

                    <p>
                        Your application <strong>must be supported</strong> by a <strong>minimum</strong> of
                        {{ $application->stage_references_quantity }} referees.
                    </p>
                    <p>
                        Your referees <strong>must</strong> be staff members in your home division, and <strong>one
                            must</strong>
                        be your Training Manager/Director.
                    </p>
                    <p><br /></p>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        {!! Form::open(["route" => ["visiting.application.referees.post"], "method" => "POST"]) !!}

                        <div class="col-md-6">
                            {!! ControlGroup::generate(
                                Form::label("referee_cid","Referee CID"),
                                Form::text("referee_cid"),
                                Form::help("Please ensure this is correct.")
                            ) !!}

                            {!! ControlGroup::generate(
                                Form::label("referee_position","Staff Position"),
                                Form::text("referee_position")
                            ) !!}

                        </div>

                        <div class="col-md-6">
                            {!! ControlGroup::generate(
                                Form::label("referee_email", "Email Address"),
                                Form::text("referee_email"),
                                Form::help("Thiss hould be the member's staff email address.")
                            ) !!}

                            <div class="text-center" style="padding-top: 27px;">
                                {!! Button::primary("ADD REFEREE")->submit() !!}
                            </div>
                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>CID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($application->referees as $referee)
                            <tr>
                                <td>{{ $referee->account_id }}</td>
                                <td>{{ $referee->account->name }}</td>
                                <td>{{ $referee->email }}</td>
                                <td>{{ $referee->relationship }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop
