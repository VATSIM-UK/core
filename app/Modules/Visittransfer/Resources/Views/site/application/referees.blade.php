@extends('visittransfer::site.application._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("References &amp; Referees", ["type" => "fa", "key" => "comment-o"]) !!}
            <div class="row">

                <div class="col-md-6 col-md-offset-3">

                    <p>
                        <span id="minReferencesHelp">
                            Your application <strong>must be supported</strong> by a <strong>minimum</strong> of
                            {{ $application->references_required }} referee(s).
                        </span>

                        @if($application->number_references_required_relative)
                            You still need to add <strong>{{ $application->number_references_required_relative }}</strong> more referee(s).
                        @else
                            You cannot add any more referees.
                        @endif
                    </p>
                    <p>
                        <span id="divisionStaffHelp">Your referees <strong>must</strong> be staff members in your home division</span>,
                        and <span id="trainingStaffHelp"><strong>one must</strong> be your Training Director</span>.
                    </p>
                    <p><br /></p>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        @if($application->number_references_required_relative > 0)
                            {!! Form::open(["route" => ["visiting.application.referees.post"], "method" => "POST"]) !!}

                            <div class="col-md-6">
                                {!! ControlGroup::generate(
                                    Form::label("referee_cid","Referee CID"),
                                    Form::text("referee_cid"),
                                    Form::help("Please ensure this is correct.")
                                )->withAttributes(["id" => "refereeCidHelp"]) !!}

                                {!! ControlGroup::generate(
                                    Form::label("referee_relationship","Staff Position"),
                                    Form::text("referee_relationship")
                                )->withAttributes(["id" => "refereePositionHelp"]) !!}

                            </div>

                            <div class="col-md-6">
                                {!! ControlGroup::generate(
                                    Form::label("referee_email", "Email Address"),
                                    Form::text("referee_email"),
                                    Form::help("This should be the member's staff email address.")
                                )->withAttributes(["id" => "refereeEmail"]) !!}

                                <div class="text-center" style="padding-top: 27px;">
                                    {!! Button::primary("ADD REFEREE")->submit() !!}
                                </div>
                            </div>

                            {!! Form::close() !!}
                        @else
                            <div class="col-md-12 text-center">
                                <p>You cannot add any additional referees at this time.</p>
                            </div>
                        @endif
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