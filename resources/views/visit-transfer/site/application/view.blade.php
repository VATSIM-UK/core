@extends(($application->is_not_editable) ? 'visit-transfer.site.application._layout_single' : 'visit-transfer.site.application._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            @include('components.html.panel_open', [
                'title' => 'Application #'.$application->public_id.' - '.$application->type_string.' '.$application->facility_name,
                'icon' => ['type' => 'fa', 'key' => 'question']
            ])
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    @if($application->will_auto_accept && $application->is_submitted)
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
                        <strong>Facility:</strong> {{ $application->facility_name }}
                    </p>

                    <p>
                        <strong>Training Required:</strong>
                        @if($application->training_required)
                            <span class="label label-success">YES</span>
                        @else
                            <span class="label label-error">NO</span>
                        @endif
                    </p>

                </div>

                <div class="col-md-10 col-md-offset-1">
                    <pre><strong>Supporting Statement</strong><br />{{ $application->statement_required ? $application->statement : "No statement required." }}</pre>
                </div>


                <div class="col-md-10 col-md-offset-1">
                    <h3>Application Status - {{ $application->status_string }}</h3>
                    @if($application->is_in_progress)
                        <div class="alert alert-info" role="alert">
                            <p>
                                This application has not yet been submitted.
                            </p>
                        </div>
                    @elseif($application->is_cancelled)
                        <div class="alert alert-danger" role="alert">
                            <p>
                                This application has been cancelled.
                            </p>
                        </p>
                    @elseif($application->is_under_review)
                        <div class="alert alert-warning" role="alert">
                            <p>
                                We are reviewing your application. You will be notified of the outcome.
                            </p>
                        </div>
                    @elseif($application->is_rejected)
                        <div class="alert alert-warning" role="alert">
                            <p>
                                Your application has been rejected.  The reason provided was:<br />
                                {!! nl2br($application->status_note) !!}
                            </p>
                        </div>
                    @elseif($application->is_accepted)
                        <div class="alert alert-warning" role="alert">
                            <p>
                                Congratulations, your application has been accepted! You will be contacted to discuss the next steps.
                            </p>
                            @if($application->training_required)
                                <p class="text-danger">
                                    Your application cannot be completed without receiving training from the {{ strtoupper($application->training_team) }} department.
                                    They will be in touch to discuss this with you.<br />
                                </p>
                            @endif
                        </div>
                    @else<div class="alert alert-info" role="alert">
                        <p>
                            Your application is currently undergoing some final automated checks.  It will automatically be submitted for review once these are complete.
                        </p>
                    </div>
                    @endif
                </div>

            </div>
                @can("withdraw-application", $application)
                <div class="col-md-10 col-md-offset-1">
                    <h3>Withdraw Application</h3>
                    <p>If you wish to withdraw your application, please click below.</p>
                    <a href="{{ route('visiting.application.withdraw', [$application->public_id]) }}" class="label label-danger label-md">Withdraw Application</a>
                </div>
                @endif
            @include('components.html.panel_close')
    </div>
@stop
