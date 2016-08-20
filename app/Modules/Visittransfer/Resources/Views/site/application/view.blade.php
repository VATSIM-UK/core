@extends(($application->is_not_editable) ? 'visittransfer::site.application._layout_single' : 'visittransfer::site.application._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("Application #".$application->public_id." - ".$application->type_string." ".$application->facility_name, ["type" => "fa", "key" => "question"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    @if($application->will_be_auto_accepted && $application->is_submitted)
                        <div class="alert alert-success" role="alert">
                            <p>
                                This application has been accepted pending the automated verification process.
                                If this process completes successfully, your application will be accepted.  You will be
                                notified of the outcome within the next {{ $application->submitted_at->addHours(1)->diffInMinutes(\Carbon\Carbon::now()) }}
                                minutes.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="col-md-4 col-md-offset-1">

                    <p>
                        <strong>Type of Application:</strong> {{ $application->type_string }}
                    </p>

                    <p>
                        <strong>Facility:</strong> {{ $application->facility->name }}
                    </p>

                    <p>
                        <strong>Training Required:</strong>
                        @if($application->is_training_required)
                            <span class="label label-success">YES</span>
                        @else
                            <span class="label label-error">NO</span>
                        @endif
                    </p>

                </div>

                <div class="col-md-6">

                    @if($application->referees->count() > 0)
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>CID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($application->referees as $referee)
                                <tr>
                                    <td>{{ $referee->account_id }}</td>
                                    <td>{{ $referee->account->name }}</td>
                                    <td>{{ $referee->email }}</td>
                                    <td>{{ $referee->relationship }}</td>
                                    <td>{{ $referee->status_string }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <table class="table table-striped table-hover">
                            <tbody>
                                <tr>
                                    <th class="text-center">
                                        @if($application->facility->stage_reference_enabled)
                                            You have no references.
                                        @else
                                            Your application does not require any references.
                                        @endif
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                </div>

                <div class="col-md-10 col-md-offset-1">
                    <pre><strong>Supporting Statement</strong><br />{{ $application->facility->stage_statement_enabled ? $application->statement : "No statement required." }}</pre>
                </div>


                <div class="col-md-10 col-md-offset-1">
                    <h3>Application Status</h3>
                    @if($application->is_in_progress)
                        <div class="alert alert-info" role="alert">
                            <p>
                                This application has not yet been submitted.
                            </p>
                        </div>
                    @elseif($application->is_submitted)
                        @if($application->is_pending_references)
                            <div class="alert alert-danger" role="alert">
                                <p>
                                    Your references have been contacted and we are awaiting submission of their reference details.  We will notify you when this occurs.
                                </p>
                            </div>
                        @elseif($application->is_under_review)
                            <div class="alert alert-warning" role="alert">
                                <p>
                                    We have received your references and we are reviewing your application.  You will be notified of the outcome.
                                </p>
                            </div>
                        @elseif($application->is_rejected)
                            <div class="alert alert-warning" role="alert">
                                <p>
                                    Your application has been rejected.
                                </p>
                            </div>
                        @elseif($application->is_accepted)
                            <div class="alert alert-warning" role="alert">
                                <p>
                                    Your application has been accepted! Congratulations.  You will be contacted to discuss the next steps.
                                </p>
                            </div>
                        @else<div class="alert alert-info" role="alert">
                            <p>
                                Your application is currently undergoing some final automated checks.  It will automatically be submitted for review once these are complete.
                            </p>
                        </div>
                        @endif
                    @endif
                </div>

            </div>
            {!! HTML::panelClose() !!}
    </div>
@stop
