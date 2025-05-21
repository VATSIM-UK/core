@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        Create New Facility
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    @if(isset($facility) && $facility->exists)
                        <form method="POST" action="{{ route('adm.visiting.facility.update.post', $facility->id) }}">
                            @csrf
                    @else
                        <form method="POST" action="{{ route('adm.visiting.facility.create.post') }}">
                            @csrf
                    @endif

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $facility->name) }}">
                            </div>
                            <div class="form-group">
                                {!! Form::label('open', 'Open for applications?'),
                                    Form::select("open", ["1" => "YES", "0" => "NO"], Request::old("open", $facility->open), ['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group" style="margin-bottom: 25px;">
                                {!! Form::label('description', 'Description:'),
                                Form::textarea('description', Request::old("description", $facility->description), ["rows" => 9, "class" => "form-control"]) !!}

                                <div class="form-group">
                                    {!! Form::label('training_team', 'Which team are they part of?'),
                                    Form::select("training_team", ['atc' => "ATC Training", "pilot" => "Pilot Training"], Request::old("training_team", $facility->training_team), ['class' => 'form-control']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('can_visit', 'Can people VISIT this facility?'),
                                    Form::select("can_visit", ["1" => "YES", "0" => "NO"], Request::old("can_visit", $facility->can_visit), ['class' => 'form-control']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('can_transfer', 'Can people TRANSFER TO this facility?'),
                                    Form::select("can_transfer", ["1" => "YES", "0" => "NO"], Request::old("can_transfer", $facility->can_transfer), ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="row">

                                @if(isset($facility) && $facility->exists)
                                    <div class="col-md-9">
                                 @else
                                    <div class="col-md-12">
                                 @endif
                                    <div class="form-group">
                                        {!! Form::label('public', 'Should this facility be displayed as an option for every applicant?'),
                                        Form::select("public", [0 => "No", 1 => "Yes"], Request::old("public", $facility->public), ['class' => 'form-control']) !!}
                                    </div>
                                </div>

                                @if(isset($facility) && $facility->exists)
                                    <div class="col-md-3">
                                        <p>
                                            <b>Manual Join Key:</b>
                                        </p>
                                        <p>
                                            <font size="4em" style="word-break: break-word;">
                                                @if ($facility->public)
                                                    <i>N/A</i>
                                                @else
                                                    <i data-toggle="tooltip"
                                                       title="Give this key to applicants so that they can apply to join this facility">{!!$facility->public_id!!}</i>
                                                @endif
                                            </font>
                                        </p>
                                    </div>
                                @endif

                            </div>

                            <div class="form-group">
                                {!! Form::label('training_required', 'Is training required?'),
                                Form::select("training_required", [0 => "No", 1 => "Yes"], Request::old("training_required", $facility->training_required), ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('training_spaces', 'How many training places are available?'),
                                Form::select("training_spaces", [null => "Infinite", 0,1,2,3,4,5,6,7,8,9,10], Request::old("training_spaces", ($facility->training_spaces === null ? "null" : $facility->training_spaces)), ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!!Form::label('stage_statement_enabled', 'Is a statement required?'),
                                Form::select("stage_statement_enabled", [0 => "No", 1 => "Yes"], Request::old("stage_statement_enabled", $facility->stage_statement_enabled), ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!!Form::label('stage_reference_enabled', 'Are references required?'),
                                Form::select("stage_reference_enabled", [0 => "No", 1 => "Yes"], Request::old("stage_reference_enabled", $facility->stage_reference_enabled), ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('stage_reference_quantity', 'How many references are required?'),
                                Form::select("stage_reference_quantity", [0,1,2,3,4,5,6,7,8,9,10], Request::old("stage_reference_quantity", $facility->stage_reference_quantity), ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('stage_checks', 'Do you want the automated checks to run?'),
                                Form::select("stage_checks", [0 => "No", 1 => "Yes"], Request::old("stage_checks", $facility->stage_checks), ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('auto_acceptance', 'Automatically accept all applicants?'),
                                Form::select("auto_acceptance", [0 => "No", 1 => "Yes"], Request::old("auto_acceptance", $facility->auto_acceptance), ['class' => 'form-control']) !!}
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h3> Notification Emails </h3>
                            <small> These email addresses will be sent an email once an application to this facility
                                is succesful. If no email addresses are entered (i.e all inputs left blank), this
                                will default to {{ $facility->training_team }}-team@vatsim.uk
                            </small>
                            <div class="row" id="notification-emails">
                                @for ($i = 0; $i < (($emails->count() < 3) ? 3 : $emails->count() ); $i++)
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="acceptance_emails[]" class="control-label">Email:</label>
                                            <input class="form-control" name="acceptance_emails[]" type="text"
                                                   id="acceptance_emails[]"
                                                   value="{{ ($emails->count() - 1 < $i) ? old("acceptance_emails." . $i) : old("acceptance_emails." . $i, $emails[$i]->email) }}">
                                        </div>
                                    </div>
                                @endfor
                                <div style="display:none" id="acceptance-email-copy">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="acceptance_emails[]" class="control-label">Email:</label>
                                            <input class="form-control" name="acceptance_emails[]" type="text"
                                                   id="acceptance_emails[]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-md-offset-4 text-center">
                                        <span class="btn btn-default"
                                              onclick="addNotificationEmailInput(); return false"><i
                                                    class="ion ion-plus-round"></i> Add another email</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        <div class="btn-group pull-right">
                            <button type="submit"
                                    class="btn btn-success">{{ (isset($facility) && $facility->exists ? "Update" : "Create")." Facility" }}</button>
                        </div>
                    </div>

                    {!! Form::close() !!}
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop

@section('scripts')
    @parent
    <script type="text/javascript">
        function addNotificationEmailInput() {
            var newinput = $("#acceptance-email-copy").html()
            $("#notification-emails").html($("#notification-emails").html() + newinput);
        }
    </script>
@stop
