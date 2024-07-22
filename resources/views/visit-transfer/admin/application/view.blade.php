@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-warning">
                <div class="box-header">
                    <div class="box-title">Administrative Actions</div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            @can("reject", $application)
                                <button class="btn btn-danger pull-left" data-toggle="modal"
                                        data-target="#modalApplicationReject">Reject Application
                                </button>
                            @endcan

                            @can("accept", $application)
                                <button class="btn btn-success pull-right" data-toggle="modal"
                                        data-target="#modalApplicationAccept">Accept Application
                                </button>
                            @endcan

                            @can("cancel", $application)
                                <button class="btn btn-danger pull-right" data-toggle="modal"
                                        data-target="#modalApplicationCancel">Mark Application Cancelled
                                </button>
                            @endcan

                            @can("complete", $application)
                                <button class="btn btn-info pull-right margin-r-5" data-toggle="modal"
                                        data-target="#modalApplicationComplete">Mark Application Complete
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">

                            <h3>Application Content #{{ $application->public_id }}</h3>

                            <table class="table table-striped table-bordered table-hover">
                                <tbody>
                                <tr>
                                    <th class="col-md-2">Status</th>
                                    <td>
                                        @include("visit-transfer.partials.application_status", ["application" => $application])
                                    </td>
                                </tr>
                                <tr>
                                    <th class="col-md-2">Applicant</th>
                                    <td>
                                        {{ $application->account->name }} {{ $application->account->id }}
                                        ({{ $application->account->email }})
                                    </td>
                                </tr>
                                <tr>
                                    <th class="col-md-2">Current Rating</th>
                                    <td>
                                        @empty($application->account->qualification_atc)
                                            Unknown ATC
                                        @else
                                            @include("mship.partials._qualification", ["qualification" => $application->account->qualification_atc])
                                        @endempty
                                        /
                                        {{ $application->account->qualifications_pilot_string }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="col-md-2">Memberships</th>
                                    <td>
                                        @foreach($application->account->states->merge($application->account->statesHistory) as $state)
                                            @php
                                                $output = $state->name;
                                                if ($state->pivot->region && $state->pivot->division)
                                                $output .= ' ('.$state->pivot->region.'/'.$state->pivot->division.')';
                                                $output .= ' from '.$state->pivot->start_at.' to '.($state->pivot->end_at ?: 'present');
                                            @endphp
                                            <p>{{ $output }}</p>
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $application->type_string }}</td>
                                </tr>
                                <tr>
                                    <th>Facility</th>
                                    <th>{{ $application->facility_name }}</th>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <th>
                                        {{ $application->created_at->diffForHumans() }}
                                        //
                                        {{ $application->created_at }}
                                    </th>
                                </tr>
                                <tr>
                                    <th>Submitted At</th>
                                    <th>
                                        @if($application->submitted_at)
                                            {{ $application->submitted_at->diffForHumans() }}
                                            //
                                            {{ $application->submitted_at }}
                                        @else
                                            Not yet submitted
                                        @endif
                                    </th>
                                </tr>
                                <tr>
                                    <th>Statement</th>
                                    <th>
                                        {!! ($application->statement ? nl2br($application->statement) : "None Supplied") !!}
                                    </th>
                                </tr>
                            </table>

                            @if($application->referees()->withTrashed()->whereNotNull('deleted_at')->first() && $application->referees->count() > 0)
                                <p class="text-center"><strong><i>Application has system deleted references in addition
                                            to the below:</i></strong></p>
                            @endif

                            @forelse($application->referees as $count=>$reference)
                                <br/>
                                <h4>
                                    Reference {{ $count+1 }} - {{ $reference->account->name }}

                                    @if($reference->is_rejected)
                                        - REJECTED
                                    @elseif($reference->is_accepted)
                                        - ACCEPTED
                                    @elseif($reference->is_cancelled)
                                        - CANCELLED
                                    @endif

                                    <small>DBID: {{ $reference->id }}</small>
                                </h4>
                                <table class="table table-striped table-bordered table-condensed">
                                    <tr>
                                        <th class="col-md-2">Referee</th>
                                        <td>
                                            {{ $reference->account->name }} {{ $reference->account->id }} identified as
                                            {{ $reference->relationship }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Referee Rating</th>
                                        <td>
                                            @empty($reference->account->qualification_atc)
                                                Unknown
                                            @else
                                                @include("mship.partials._qualification", ["qualification" => $reference->account->qualification_atc])
                                            @endempty
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Memberships</th>
                                        <td>
                                            @foreach($reference->account->states->merge($reference->account->statesHistory) as $state)
                                                @php
                                                    $output = $state->name;
                                                    if ($state->pivot->region && $state->pivot->division)
                                                    $output .= ' ('.$state->pivot->region.'/'.$state->pivot->division.')';
                                                    $output .= ' from '.$state->pivot->start_at.' to '.($state->pivot->end_at ?: 'present');
                                                @endphp
                                                <p>{{ $output }}</p>
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Reference</th>
                                        <td>{!! nl2br($reference->reference) !!}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            @if($reference->status == \App\Models\VisitTransferLegacy\Reference::STATUS_UNDER_REVIEW)
                                                <button class="btn btn-danger pull-left" data-toggle="modal"
                                                        data-target="{{ "#modalReferenceReject" . $reference->id }}">
                                                    Reject Reference
                                                </button>

                                                <button class="btn btn-success pull-right" data-toggle="modal"
                                                        data-target="{{ "#modalReferenceAccept" . $reference->id }}">
                                                    Accept Reference
                                                </button>
                                            @else
                                                <strong>Status Note</strong>
                                                : {{ $reference->status_note ? $reference->status_note : "No note added" }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            @foreach($reference->notes as $note)
                                                @include('adm.mship.account._note', ["note" => $note])
                                            @endforeach
                                        </td>
                                    </tr>
                                </table>
                            @empty
                                @if($application->references_required > 0)
                                    <p class="text-center">There are no references associated with this application.</p>
                                @endif
                            @endforelse

                        </div>

                        <div class="col-md-6">
                            <h3>Stages &amp; Automated Checks</h3>

                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="col-md-2 text-center">Facility</th>
                                    <th class="col-md-2 text-center">Training Required</th>
                                    <th class="col-md-2 text-center">Statement</th>
                                    <th class="col-md-2 text-center">References</th>
                                    <th class="col-md-2 text-center">Auto Check</th>
                                    <th class="col-md-2 text-center">Auto Accept</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="text-center">
                                        @if($application->facility)
                                            {!! HTML::img("tick_mark_circle", "png", 24) !!}
                                        @else
                                            {!! HTML::img("cross_mark_circle", "png", 24) !!}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$application->facility)
                                            {!! HTML::img("vertical_mark", "png", 24, 32) !!}
                                        @elseif($application->training_required)
                                            {!! HTML::img("tick_mark_circle", "png", 24) !!}
                                        @else
                                            {!! HTML::img("cross_mark_circle", "png", 24) !!}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$application->facility)
                                            {!! HTML::img("vertical_mark", "png", 24, 32) !!}
                                        @elseif($application->statement_required)
                                            {!! HTML::img("tick_mark_circle", "png", 24) !!}
                                        @else
                                            {!! HTML::img("cross_mark_circle", "png", 24) !!}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$application->facility)
                                            {!! HTML::img("vertical_mark", "png", 24, 32) !!}
                                        @elseif($application->references_required > 0)
                                            {!! HTML::img("tick_mark_circle", "png", 24) !!}
                                        @else
                                            {!! HTML::img("cross_mark_circle", "png", 24) !!}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$application->facility)
                                            {!! HTML::img("vertical_mark", "png", 24, 32) !!}
                                        @elseif($application->should_perform_checks)
                                            {!! HTML::img("tick_mark_circle", "png", 24) !!}
                                        @else
                                            {!! HTML::img("cross_mark_circle", "png", 24) !!}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$application->facility)
                                            {!! HTML::img("vertical_mark", "png", 24, 32) !!}
                                        @elseif($application->will_auto_accept)
                                            {!! HTML::img("tick_mark_circle", "png", 24) !!}
                                        @else
                                            {!! HTML::img("cross_mark_circle", "png", 24) !!}
                                        @endif
                                    </td>
                                </tr>
                                @can("setting-toggle", $application)
                                    <tr>
                                        <td class="text-center">
                                            -
                                        </td>
                                        <td class="text-center">
                                            {!! Form::open(array("url" => URL::route("adm.visiting.application.setting.toggle.post", $application->id))) !!}
                                            <button type="submit" class="btn btn-warning btn-xs">Toggle</button>
                                            {!! Form::hidden("setting", "training_required") !!}
                                            {!! Form::close() !!}
                                        </td>
                                        <td class="text-center">
                                            {!! Form::open(array("url" => URL::route("adm.visiting.application.setting.toggle.post", $application->id))) !!}
                                            <button type="submit" class="btn btn-warning btn-xs">Toggle</button>
                                            {!! Form::hidden("setting", "statement_required") !!}
                                            {!! Form::close() !!}
                                        </td>
                                        <td class="text-center">
                                            {!! Form::open(array("url" => URL::route("adm.visiting.application.setting.toggle.post", $application->id))) !!}
                                            <button type="submit" class="btn btn-warning btn-xs">Toggle</button>
                                            {!! Form::hidden("setting", "references_required") !!}
                                            {!! Form::close() !!}
                                        </td>
                                        <td class="text-center">
                                            {!! Form::open(array("url" => URL::route("adm.visiting.application.setting.toggle.post", $application->id))) !!}
                                            <button type="submit" class="btn btn-warning btn-xs">Toggle</button>
                                            {!! Form::hidden("setting", "should_perform_checks") !!}
                                            {!! Form::close() !!}
                                        </td>
                                        <td class="text-center">
                                            {!! Form::open(array("url" => URL::route("adm.visiting.application.setting.toggle.post", $application->id))) !!}
                                            <button type="submit" class="btn btn-warning btn-xs">Toggle</button>
                                            {!! Form::hidden("setting", "will_auto_accept") !!}
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endcan
                                </tbody>
                            </table>

                            <br/>

                            <table class="table table-bordered">
                                <tr class="bg-info">
                                    <th class="col-md-2">
                                        Current Status
                                    </th>
                                    <th>
                                        {{ $application->status_string }}
                                        {!! $application->status_note ? "<br /><small>" . $application->status_note . "</small>" : "" !!}
                                    </th>
                                </tr>
                                <tr class="bg-{{ $application->references_accepted->count() == $application->references_required ? "success" : "danger" }}">
                                    <th class="col-md-2">
                                        Reference Check
                                    </th>
                                    <th>
                                        @if($application->references_required < 1)
                                            References <strong class="text-danger">are not required</strong>.
                                        @elseif($application->is_pending_references)
                                            Some references <strong class="text-danger">have not been submitted</strong>
                                            .
                                        @elseif($application->references_required != $application->references_accepted->count())
                                            Some references <strong class="text-danger">have not been accepted</strong>.
                                        @else
                                            All references <strong class="text-danger">have been accepted</strong>.
                                        @endif
                                    </th>
                                </tr>
                                <tr class="bg-{{ $application->check_outcome_90_day ? "success" : "danger" }}">
                                    <th class="col-md-2">
                                        90 Day Check
                                    </th>
                                    <th>
                                        @if(!$application->submitted_at)
                                            Application not submitted, so this cannot be checked.
                                        @elseif($application->check_outcome_90_day)
                                            Qualification awarded <strong class="text-danger">in excess</strong> of 90
                                            days prior to application submission.<br/>

                                            @can("check-outcome", $application)
                                                <button type="button" class="btn btn-danger btn-xs" data-toggle="modal"
                                                        data-target="#90DayNotMet">
                                                    MARK THIS CHECK AS 'NOT MET'
                                                </button>
                                                <div class="modal fade" role="dialog" id="90DayNotMet">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                Mark 90 Day Qualification Check as 'NOT MET'?
                                                            </div>
                                                            <div class="modal-body">
                                                                Once you have manually verified that this member
                                                                received their current qualification within 90 days
                                                                prior to the submission date, you can mark it as 'NOT
                                                                MET'.
                                                            </div>
                                                            <div class="modal-footer">
                                                                {{ Form::open(array("url" => URL::route("adm.visiting.application.check.notmet.post", $application->id))) }}
                                                                <button type="submit" class="btn btn-danger">MARK THIS
                                                                    CHECK AS 'NOT MET' - THIS CANNOT BE UNDONE
                                                                </button>
                                                                {{ Form::hidden("check", "90_day") }}
                                                                {{ Form::close() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan

                                        @elseif($application->check_outcome_90_day === null)
                                            <strong class="text-danger">Data unavailable</strong> - manual check
                                            required.<br/>

                                            @can("check-outcome", $application)
                                                <button type="button" class="btn btn-danger btn-xs" data-toggle="modal"
                                                        data-target="#90DayNotMet">
                                                    MARK THIS CHECK AS 'NOT MET'
                                                </button>
                                                <div class="modal fade" role="dialog" id="90DayNotMet">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                Mark 90 Day Qualification Check as 'NOT MET'?
                                                            </div>
                                                            <div class="modal-body">
                                                                Once you have manually verified that this member
                                                                received their current qualification within 90 days
                                                                prior to the submission date, you can mark it as 'NOT
                                                                MET'.
                                                            </div>
                                                            <div class="modal-footer">
                                                                {{ Form::open(array("url" => URL::route("adm.visiting.application.check.notmet.post", $application->id))) }}
                                                                <button type="submit" class="btn btn-danger">MARK THIS
                                                                    CHECK AS 'NOT MET' - THIS CANNOT BE UNDONE
                                                                </button>
                                                                {{ Form::hidden("check", "90_day") }}
                                                                {{ Form::close() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="button" class="btn btn-success btn-xs" data-toggle="modal"
                                                        data-target="#90DayMet">
                                                    MARK THIS CHECK AS 'MET'
                                                </button>
                                                <div class="modal fade" role="dialog" id="90DayMet">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                Mark 90 Day Qualification Check as 'MET'?
                                                            </div>
                                                            <div class="modal-body">
                                                                Once you have manually verified that this member
                                                                received their current qualification in excess of 90
                                                                days prior to the submission date, you can mark it as
                                                                'MET'.
                                                            </div>
                                                            <div class="modal-footer">
                                                                {{ Form::open(array("url" => URL::route("adm.visiting.application.check.met.post", $application->id))) }}
                                                                <button type="submit" class="btn btn-success">MARK THIS
                                                                    CHECK AS 'MET' - THIS CANNOT BE UNDONE
                                                                </button>
                                                                {{ Form::hidden("check", "90_day") }}
                                                                {{ Form::close() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan

                                        @elseif(!$application->check_outcome_90_day)
                                            Qualification awarded <strong class="text-danger">within</strong> 90 days
                                            prior to application submission.<br/>

                                            @can("check-outcome", $application)
                                                <button type="button" class="btn btn-success btn-xs" data-toggle="modal"
                                                        data-target="#90DayMet">
                                                    MARK THIS CHECK AS 'MET'
                                                </button>
                                                <div class="modal fade" role="dialog" id="90DayMet">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                Mark 90 Day Qualification Check as 'MET'?
                                                            </div>
                                                            <div class="modal-body">
                                                                Once you have manually verified that this member
                                                                received their current qualification in excess of 90
                                                                days prior to the submission date, you can mark it as
                                                                'MET'.
                                                            </div>
                                                            <div class="modal-footer">
                                                                {{ Form::open(array("url" => URL::route("adm.visiting.application.check.met.post", $application->id))) }}
                                                                <button type="submit" class="btn btn-success">MARK THIS
                                                                    CHECK AS 'MET' - THIS CANNOT BE UNDONE
                                                                </button>
                                                                {{ Form::hidden("check", "90_day") }}
                                                                {{ Form::close() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                        @endif
                                    </th>
                                </tr>
                                <tr class="bg-{{ $application->check_outcome_50_hours ? "success" : "danger" }}">
                                    <th>
                                        50 Hour Check
                                    </th>
                                    <th>
                                        @if(!$application->submitted_at)
                                            Application not submitted, so this cannot be checked.
                                        @elseif($application->check_outcome_50_hours)
                                            Applicant has <strong class="text-danger">in excess</strong> of 50 hours at
                                            their present qualified level and has ratified their rating.<br/>

                                            @can("check-outcome", $application)
                                                <button type="button" class="btn btn-danger btn-xs" data-toggle="modal"
                                                        data-target="#50HourNotMet">
                                                    MARK THIS CHECK AS 'NOT MET'
                                                </button>
                                                <div class="modal fade" role="dialog" id="50HourNotMet">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                Mark 50 Hour Check as 'NOT MET'?
                                                            </div>
                                                            <div class="modal-body">
                                                                Once you have manually verified that this member has not
                                                                ratified their rating (50 hours at rating, on a relevant
                                                                position in their home division), you can mark it as
                                                                'NOT MET'.
                                                            </div>
                                                            <div class="modal-footer">
                                                                {{ Form::open(array("url" => URL::route("adm.visiting.application.check.notmet.post", $application->id))) }}
                                                                <button type="submit" class="btn btn-danger">MARK THIS
                                                                    CHECK AS 'NOT MET' - THIS CANNOT BE UNDONE
                                                                </button>
                                                                {{ Form::hidden("check", "50_hours") }}
                                                                {{ Form::close() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                        @elseif($application->check_outcome_50_hours === null)
                                            <strong class="text-danger">Data unavailable</strong> - manual check
                                            required.<br/>

                                            @can("check-outcome", $application)
                                                <button type="button" class="btn btn-danger btn-xs" data-toggle="modal"
                                                        data-target="#50HourNotMet">
                                                    MARK THIS CHECK AS 'NOT MET'
                                                </button>
                                                <div class="modal fade" role="dialog" id="50HourNotMet">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                Mark 50 Hour Check as 'NOT MET'?
                                                            </div>
                                                            <div class="modal-body">
                                                                Once you have manually verified that this member has not
                                                                ratified their rating (50 hours at rating, on a relevant
                                                                position in their home division), you can mark it as
                                                                'NOT MET'.
                                                            </div>
                                                            <div class="modal-footer">
                                                                {{ Form::open(array("url" => URL::route("adm.visiting.application.check.notmet.post", $application->id))) }}
                                                                <button type="submit" class="btn btn-danger">MARK THIS
                                                                    CHECK AS 'NOT MET' - THIS CANNOT BE UNDONE
                                                                </button>
                                                                {{ Form::hidden("check", "50_hours") }}
                                                                {{ Form::close() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="button" class="btn btn-success btn-xs" data-toggle="modal"
                                                        data-target="#50HourMet">
                                                    MARK THIS CHECK AS 'MET'
                                                </button>
                                                <div class="modal fade" role="dialog" id="50HourMet">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                Mark 50 Hour Check as 'MET'?
                                                            </div>
                                                            <div class="modal-body">
                                                                Once you have manually verified that this member has
                                                                ratified their rating (50 hours at rating, on a relevant
                                                                position in their home division), you can mark it as
                                                                'MET'.
                                                            </div>
                                                            <div class="modal-footer">
                                                                {{ Form::open(array("url" => URL::route("adm.visiting.application.check.met.post", $application->id))) }}
                                                                <button type="submit" class="btn btn-success">MARK THIS
                                                                    CHECK AS 'MET' - THIS CANNOT BE UNDONE
                                                                </button>
                                                                {{ Form::hidden("check", "50_hours") }}
                                                                {{ Form::close() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan

                                        @elseif(!$application->check_outcome_50_hours)
                                            Applicant <strong class="text-danger">does not have in excess</strong> of 50
                                            hours at their present qualified level.<br/>

                                            @can("check-outcome", $application)
                                                <button type="button" class="btn btn-success btn-xs" data-toggle="modal"
                                                        data-target="#50HourMet">
                                                    MARK THIS CHECK AS 'MET'
                                                </button>
                                                <div class="modal fade" role="dialog" id="50HourMet">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                Mark 50 Hour Check as 'MET'?
                                                            </div>
                                                            <div class="modal-body">
                                                                Once you have manually verified that this member has
                                                                ratified their rating (50 hours at rating, on a relevant
                                                                position in their home division), you can mark it as
                                                                'MET'.
                                                            </div>
                                                            <div class="modal-footer">
                                                                {{ Form::open(array("url" => URL::route("adm.visiting.application.check.met.post", $application->id))) }}
                                                                <button type="submit" class="btn btn-success">MARK THIS
                                                                    CHECK AS 'MET' - THIS CANNOT BE UNDONE
                                                                </button>
                                                                {{ Form::hidden("check", "50_hours") }}
                                                                {{ Form::close() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan

                                        @endif
                                    </th>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>

        <div class="col-md-12">
            <h3>Member Notes</h3>
            @foreach($application->account->notes as $note)
                @include('adm.mship.account._note', ["note" => $note])
            @endforeach
        </div>
    </div>
    </div>



    @if($unacceptedReferences->count() == 0)
        <div class="modal fade" id="modalApplicationReject" tabindex="-1" role="dialog"
             aria-labelledby="Reject Application" aria-hidden="true">
            {!! Form::open(array("url" => URL::route("adm.visiting.application.reject.post", $application->id))) !!}
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">
                            Reject Application #{{ $application->public_id }}
                            - {{ $application->type_string }} {{ $application->facility_name }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            You can reject an application by entering a reason below. The reasons entered in the public
                            boxes <strong>will</strong> be
                            sent to the applicant. The <strong>staff note</strong> content will not. <strong
                                    class="text-danger">This action cannot be undone</strong>.
                        </p>
                        <div class="form-group">
                            <label for="rejection_reason">Rejection Reason</label>
                            <select name="rejection_reason" class="form-control">
                                <option>
                                    Negative Reference
                                </option>
                                <option>
                                    Non-compliant with Visiting &amp; Transferring Policy
                                </option>
                                <option value="other">
                                    Other Reason
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="rejection_reason_extra">Extra Information (optional)</label>
                            <textarea name="rejection_reason_extra" class="form-control" rows="5"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="rejection_staff_note">Staff Note (mandatory)</label>
                            <textarea name="rejection_staff_note" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Reject Application</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        <div class="modal fade" id="modalApplicationAccept" tabindex="-1" role="dialog"
             aria-labelledby="Accept Application" aria-hidden="true">
            {!! Form::open(array("url" => URL::route("adm.visiting.application.accept.post", $application->id))) !!}
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">
                            Accept Application #{{ $application->public_id }}
                            - {{ $application->type_string }} {{ $application->facility_name }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            If you accept this application the applicant will be notified.
                            It is important that you understand <strong class="text-danger">this action cannot be
                                undone.</strong>
                        </p>

                        @if($application->training_required)
                            <p>
                                As training is required for this {{ $application->type_string }} to be completed, the
                                applicant
                                will be advised that the {{ strtoupper($application->training_team) }} will be in touch.
                                The {{ strtoupper($application->training_team) }} will also be notified of this
                                application via email.
                            </p>
                        @else
                            <p>
                                As training is <strong class="text-danger">not</strong> required for
                                this {{ $application->type_string }}
                                to be completed, the applicant will be advised that their application has been <strong>completed</strong>.
                                <strong class="text-danger">The {{ strtoupper($application->training_team) }} will not
                                    be notified of this.</strong>
                            </p>
                        @endif

                        <p>
                            You must write a staff note detailing why you have accepted/the next steps the applicant
                            must make.
                            <strong class="text-danger">They will not be provided a copy of this information</strong>.
                        </p>

                        <div class="form-group">
                            <label for="accept_staff_note">Staff Note (optional)</label>
                            <textarea name="accept_staff_note" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Accept Application - this cannot be undone
                        </button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        <div class="modal fade" id="modalApplicationComplete" tabindex="-1" role="dialog"
             aria-labelledby="Complete Application" aria-hidden="true">
            {!! Form::open(array("url" => URL::route("adm.visiting.application.complete.post", $application->id))) !!}
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">
                            Complete Application #{{ $application->public_id }}
                            - {{ $application->type_string }} {{ $application->facility_name }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            It is important that you understand <strong class="text-danger">this action cannot be
                                undone.</strong>
                        </p>

                        <p>
                            You must write a staff note detailing why you have completed the application.
                            <strong class="text-danger">The member will not be provided a copy of this
                                information</strong>.
                        </p>

                        <div class="form-group">
                            <label for="complete_staff_note">Staff Note (mandatory)</label>
                            <textarea name="complete_staff_note" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Complete Application - this cannot be undone
                        </button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>


        <div class="modal fade" id="modalApplicationCancel" tabindex="-1" role="dialog"
             aria-labelledby="Cancel Application" aria-hidden="true">
            {!! Form::open(array("url" => URL::route("adm.visiting.application.cancel.post", $application->id))) !!}
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">
                            Cancel Application #{{ $application->public_id }}
                            - {{ $application->type_string }} {{ $application->facility_name }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            It is important that you understand <strong class="text-danger">this action cannot be
                                undone.</strong>
                        </p>

                        <p>
                            You must enter a reason why you are cancelling this application. This message
                            <strong>will</strong> be
                            sent to the applicant. The <strong>staff note</strong> content will not.
                        </p>
                        <div class="form-group">
                            <label for="cancel_reason">Cancel Reason (mandatory)</label>
                            <textarea name="cancel_reason" class="form-control" rows="5"></textarea>
                        </div>
                        <p>
                            You must write a staff note detailing why you have cancelled the application.
                            <strong class="text-danger">The member will not be provided a copy of this
                                information</strong>.
                        </p>
                        <div class="form-group">
                            <label for="cancel_staff_note">Staff Note (mandatory)</label>
                            <textarea name="cancel_staff_note" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Application - this cannot be undone
                        </button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    @endif

    @foreach($application->referees as $reference)
        <div class="modal fade" id="modalReferenceReject{{ $reference->id }}" tabindex="-1" role="dialog"
             aria-labelledby="Reject Reference" aria-hidden="true">
            {!! Form::open(array("url" => URL::route("adm.visiting.reference.reject.post", $reference->id))) !!}
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Reject Reference #{{ $reference->id }}
                            - {{ $reference->account->name }}</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            You can reject a reference by entering a reason below. The reasons entered in the public
                            boxes <strong>will</strong> be
                            sent to the applicant. The <strong>staff note</strong> content will not.
                        </p>
                        <div class="form-group">
                            <label for="rejection_reason">Rejection Reason</label>
                            <select name="rejection_reason" class="form-control">
                                <option>
                                    Unsuitable content provided
                                </option>
                                <option>
                                    Unsuitable Referee
                                </option>
                                <option value="other">
                                    Other Reason
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="rejection_reason_extra">Extra Information (optional)</label>
                            <textarea name="rejection_reason_extra" class="form-control" rows="5"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="rejection_staff_note">Staff Note (mandatory)</label>
                            <textarea name="rejection_staff_note" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Reject Reference</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        <div class="modal fade" id="modalReferenceAccept{{ $reference->id }}" tabindex="-1" role="dialog"
             aria-labelledby="Accept Reference" aria-hidden="true">
            {!! Form::open(array("url" => URL::route("adm.visiting.reference.accept.post", $reference->id))) !!}
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Accept Reference #{{ $reference->id }}
                            - {{ $reference->account->name }}</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            If you accept this reference the applicant will be notified. <strong>They will not be
                                provided with the contents</strong>.
                            It is important that you understand <strong class="text-danger">this action cannot be
                                undone.</strong>
                        </p>

                        <p>
                            You can choose to write a staff note for this reference, for your records.
                        </p>

                        <div class="form-group">
                            <label for="accept_staff_note">Staff Note (optional)</label>
                            <textarea name="accept_staff_note" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Accept Reference - this cannot be undone</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    @endforeach
@stop
