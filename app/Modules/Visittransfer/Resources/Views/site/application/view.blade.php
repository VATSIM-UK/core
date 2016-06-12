@extends('visittransfer::site.application._layout_single')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("Application #".$application->id." - ".$application->type_string." ".$application->facility->name, ["type" => "fa", "key" => "question"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    <p>
                        Thank you for your application.  The details of application #{{ $application->id }}
                        to {{strtolower($application->type_string) }} {{ $application->facility->name }}
                        submitted {{ $application->submitted_at->diffForHumans() }} are included below for your reference.
                    </p>

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
                                <td>{{ $referee->status }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

                <div class="col-md-10 col-md-offset-1">
                    <pre><strong>Supporting Statement</strong><br />{{ $application->statement }}</pre>
                </div>

            </div>
            {!! HTML::panelClose() !!}
    </div>
@stop
