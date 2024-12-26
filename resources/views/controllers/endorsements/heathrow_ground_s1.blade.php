@extends('layout')

@section('content')

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Heathrow Ground Endorsement (S1)</div>
                <div class="panel-body">
                    <p>
                        Heathrow is one of the busiest airports on the VATSIM Network.
                    </p>
                    <p>
                        Before controlling at Heathrow, we want to ensure you have the knowledge you need
                        to provide a good service to pilots and get the most from your controlling session.
                    </p>
                    <p>
                        S1 rated controllers must hold a Heathrow Endorsement in order to control EGLL_GND and EGLL_DEL positions.
                    </p>
                    <h4>Step One</h4>
                    <p>
                        In order to control Heathrow Ground as an S1, you will need to meet
                        the following requirements:
                    </p>
                    <ul>
                        <li>
                            You must be a home member of the UK
                        </li>
                        <li>
                            You must hold an S1 rating
                        </li>
                        <li>
                            You must be active on the controller roster
                        </li>
                        <li>
                            You must hold a Gatwick Endorsement
                        </li>
                        <li>
                            You must have controlled for 50 hours at Gatwick after acquiring your endorsement
                        </li>
                    </ul>
                    <h4>Step Two</h4>
                    <p>
                        You will be added to the waiting list for Heathrow training, and be
                        given access to the 'Heathrow (S1) GMC' course. This Moodle course covers
                        Heathrow specific procedures, radiotelephony, and local flight planning restrictions.
                    </p>
                    <p>
                        Once you are close to the top of the waiting list you will be given access to to the
                        Moodle exam.
                    </p>
                    <p>
                        If you do not pass the quiz on your first attempt, there is a study period of 72 hours for you to
                        review the Moodle course and improve your knowledge before you try again.
                    </p>
                    <p>
                        When you have passed the quiz at the end of the Moodle course, you will be prompted to submit
                        another ticket to ATC TRAINING.
                    </p>
                    <h4>Step Three</h4>
                    <p>
                        Begin training toward your Heathrow Ground endorsement.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Membership Status</div>
                <div class="panel-body">
                    @if($_account->primary_state?->isDivision)
                        <p>You are a home member of the UK.</p>
                    @else
                        <p class="text-danger"><strong>You are not a home member of the UK Division. If you wish to hold
                                a Gatwick endorsement, apply to transfer to the UK
                                by {!! link_to_route("visiting.landing", "clicking here") !!}.</strong></p>
                    @endif

                    @if($onRoster)
                        <p>You are active on the controller roster.</p>
                    @else
                        <p class="text-danger">You are not active on the controller roster. If you wish to hold a
                            Gatwick endorsement you must be active on the roster.</p>
                    @endif

                    @if($hasEgkkEndorsement)
                        <p>You are endorsed to control Gatwick.</p>
                    @else
                        <p class="text-danger">You do not hold a Gatwick endorsement,
                            you must <a href="{{ route('controllers.endorsements.heathrow_ground_s1') }}">complete this before</a>
                            starting your Heathrow training.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> 50 Hours Controlling at Gatwick</div>
                <div class="panel-body">
                    <div class="progress" data-toggle="tooltip" title="Hours Controlling DEL and GND">
                        @if($hoursMet)
                            <div
                                class="progress-bar progress-bar-success"
                                role="progressbar"
                                style="width: 100%"
                                aria-valuemin="0"
                                aria-valuemax="50">
                                50+ Hrs
                            </div>
                        @endif
                        <div
                            class="progress-bar {{ $hoursMet ? 'progress-bar-success' : '' }}"
                            role="progressbar"
                            style="width: {{ $progress }}%"
                            aria-valuemin="0"
                            aria-valuemax="50">
                            {{ (round($totalHours,2)) .' Hrs' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Request Moodle Course</div>
                <div class="panel-body">
                    @if($conditionsMet)
                        <p>
                            <a href="https://helpdesk.vatsim.uk/open.php">Open a ticket with ATC Training</a>
                            to request access to the Moodle Course</p>
                    @else
                        <button class="btn btn-info center-block" disabled>Request Moodle Course</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        $('[data-toggle="tooltip"]').tooltip();
    </script>
@stop
