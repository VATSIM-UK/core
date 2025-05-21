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
                                <label for="open">Open for applications?</label>
                                <select name="open" id="open" class="form-control">
                                    <option value="1" {{ old('open', $facility->open) == '1' ? 'selected' : '' }}>YES</option>
                                    <option value="0" {{ old('open', $facility->open) == '0' ? 'selected' : '' }}>NO</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 25px;">
                                <label for="description">Description:</label>
                                <textarea name="description" id="description" rows="9" class="form-control">{{ old('description', $facility->description) }}</textarea>

                                <div class="form-group">
                                    <label for="training_team">Which team are they part of?</label>
                                    <select name="training_team" id="training_team" class="form-control">
                                        <option value="atc" {{ old('training_team', $facility->training_team) == 'atc' ? 'selected' : '' }}>ATC Training</option>
                                        <option value="pilot" {{ old('training_team', $facility->training_team) == 'pilot' ? 'selected' : '' }}>Pilot Training</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="can_visit">Can people VISIT this facility?</label>
                                    <select name="can_visit" id="can_visit" class="form-control">
                                        <option value="1" {{ old('can_visit', $facility->can_visit) == '1' ? 'selected' : '' }}>YES</option>
                                        <option value="0" {{ old('can_visit', $facility->can_visit) == '0' ? 'selected' : '' }}>NO</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="can_transfer">Can people TRANSFER TO this facility?</label>
                                    <select name="can_transfer" id="can_transfer" class="form-control">
                                        <option value="1" {{ old('can_transfer', $facility->can_transfer) == '1' ? 'selected' : '' }}>YES</option>
                                        <option value="0" {{ old('can_transfer', $facility->can_transfer) == '0' ? 'selected' : '' }}>NO</option>
                                    </select>
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
                                        <label for="public">Should this facility be displayed as an option for every applicant?</label>
                                        <select name="public" id="public" class="form-control">
                                            <option value="0" {{ old('public', $facility->public) == '0' ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ old('public', $facility->public) == '1' ? 'selected' : '' }}>Yes</option>
                                        </select>
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
                                <label for="training_required">Is training required?</label>
                                <select name="training_required" id="training_required" class="form-control">
                                    <option value="0" {{ old('training_required', $facility->training_required) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('training_required', $facility->training_required) == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="training_spaces">How many training places are available?</label>
                                <select name="training_spaces" id="training_spaces" class="form-control">
                                    <option value="" {{ old('training_spaces', $facility->training_spaces) === null ? 'selected' : '' }}>Infinite</option>
                                    @for($i = 0; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('training_spaces', $facility->training_spaces) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="stage_statement_enabled">Is a statement required?</label>
                                <select name="stage_statement_enabled" id="stage_statement_enabled" class="form-control">
                                    <option value="0" {{ old('stage_statement_enabled', $facility->stage_statement_enabled) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('stage_statement_enabled', $facility->stage_statement_enabled) == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="stage_reference_enabled">Are references required?</label>
                                <select name="stage_reference_enabled" id="stage_reference_enabled" class="form-control">
                                    <option value="0" {{ old('stage_reference_enabled', $facility->stage_reference_enabled) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('stage_reference_enabled', $facility->stage_reference_enabled) == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="stage_reference_quantity">How many references are required?</label>
                                <select name="stage_reference_quantity" id="stage_reference_quantity" class="form-control">
                                    @for($i = 0; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('stage_reference_quantity', $facility->stage_reference_quantity) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="stage_checks">Do you want the automated checks to run?</label>
                                <select name="stage_checks" id="stage_checks" class="form-control">
                                    <option value="0" {{ old('stage_checks', $facility->stage_checks) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('stage_checks', $facility->stage_checks) == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="auto_acceptance">Automatically accept all applicants?</label>
                                <select name="auto_acceptance" id="auto_acceptance" class="form-control">
                                    <option value="0" {{ old('auto_acceptance', $facility->auto_acceptance) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('auto_acceptance', $facility->auto_acceptance) == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
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

                    </form>
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
