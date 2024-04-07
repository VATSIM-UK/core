@extends('layout')

@section('content')

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Gatwick Endorsement</div>
                <div class="panel-body">
                    Gatwick is one of the busiest airports on the VATSIM network. Before controlling it, we want to
                    ensure you have the knowledge you need to provide a good service to pilots and get the most from
                    your controlling session.<br>
                    <br>
                    <h4>Step One</h4>
                    In order to control Gatwick Ground as an S1, you will need to first meet the requirements outlined
                    on this page.<br>
                    You must be a home member of the UK, be rated as an S1 and have controlled for 50 hours on GMC or
                    GMP positions.<br>
                    <br>
                    <h4>Step Two</h4>
                    You will be given access to the 'Gatwick ADC | S1 Endorsement' course. This Moodle course covers
                    Gatwick specific procedures, radiotelephony, and local flight planning restrictions.
                    There is a quiz at the end of the course with a pass mark of 90% - you must pass this quiz to
                    proceed.
                    If you do not pass the quiz on your first attempt, there is a study period of seven days for you to
                    review the Moodle course and improve your knowledge before you try again.<br>
                    When you have passed the quiz at the end of the Moodle course, you will be prompted to submit
                    another ticket to ATC TRAINING.<br>
                    <br>
                    <h4>Step Three</h4>
                    One of our Gatwick mentors will take you onto the live network, on either EGKK_GND or EGKK_DEL, and
                    offer you hints and tips as you control You will also have the chance to ask any questions that you
                    have.<br>
                    This is not a test and you will not pass or fail, rather it is an opportunity for you to practically
                    apply the skills and knowledge which you have learned through completing the Moodle course.<br>
                    You will do this until the mentor deems you ready for the Gatwick ground endorsement. Once granted
                    the endorsement, you will be able to control EGKK_GND and EGKK_DEL on the live network without
                    supervision.
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-header"><i class="fa fa-info"></i> Control 50 Hours</div>
                <div class="panel-body">
                    <div class="progress" data-toggle="tooltip" title="Hours Controlling _DEL and _GND">
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
        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Membership Status</div>
                <div class="panel-body">
                    @if($_account->primary_state?->isDivision)
                        You are a home member of the UK and no further action is required.
                    @else
                        <p class="text-danger"><strong>You are not a home member of the UK Division. If you wish to hold
                                a Gatwick endorsement, apply to transfer to the UK
                                by {!! link_to_route("visiting.landing", "clicking here") !!}.</strong></p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Request Moodle Course</div>
                <div class="panel-body">
                    Once you have completed the requirements above, you will be able to press the button below to
                    request access to the Moodle course and progress to Step 2.
                    <br><br>
                    @if($conditionsMet)
                        <a href="mailto:atc-training@vatsim.uk?Subject=Gatwick%20Endorsement%20-%20Moodle%20Request&Body=Please%20grant%20me%20access%20to%20the%20Gatwick%20Endorsement%20exam%20on%20Moodle%20as%20I%20have%20now%20met%20the%20number%20of%20hours%20required.%0A%0AFull%20Name%3A%20{{ $_account->name }}%0AVATSIM%20CID%3A%20{{ $_account->id }}"
                           style="text-decoration: none;">
                            <button class="btn btn-success center-block">Request Moodle Course</button>
                        </a>
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
