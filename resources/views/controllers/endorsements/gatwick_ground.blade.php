@extends('layout')

@section('content')

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Gatwick Endorsement</div>
                <div class="panel-body">
                    Gatwick is one of the busiest airports on the VATSIM network. Before controlling it, we want to ensure you have the knowledge you need to provide a good service to pilots and get the most from your controlling session.<br>
                    <br>
                    <h4>Step One</h4>
                    In order to control Gatwick Ground as an S1, you will need to first meet the requirements outlined on this page.<br>
                    The requirements involve you completing a number of hours on various positions around the UK, being a home member of the UK and rated as an S1.<br>
                    <br>
                    <h4>Step Two</h4>
                    You will be given access to the 'Gatwick ADC | S1 Endorsement' course. This Moodle course covers Gatwick specific procedures, radiotelephony, and local flight planning restrictions.
                    There is a quiz at the end of the course with a pass mark of 90% - you must pass this quiz to proceed.
                    If you do not pass the quiz on your first attempt, there is a study period of seven days for you to review the Moodle course and improve your knowledge before you try again.<br>
                    When you have passed the quiz at the end of the Moodle course, you will be prompted to submit another ticket to ATC TRAINING.<br>
                    <br>
                    <h4>Step Three</h4>
                    One of our Gatwick mentors will take you onto the live network, on either EGKK_GND or EGKK_DEL, and offer you hints and tips as you control You will also have the chance to ask any questions that you have.<br>
                    This is not a test and you will not pass or fail, rather it is an opportunity for you to practically apply the skills and knowledge which you have learned through completing the Moodle course.<br>
                    You will do this until the mentor deems you ready for the Gatwick ground endorsement. Once granted the endorsement, you will be able to control EGKK_GND and EGKK_DEL on the live network without supervision.
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Group One Controlling</div>
                <div class="panel-body">
                    Control a total of <strong>{{ $conditions[0]->required_hours }} hours</strong> on one of the following positions within the last <strong>{{ $conditions[0]->within_months }} months.</strong>
                    <ul>
                        <li>Manchester (EGCC)</li>
                        <li>Edinburgh (EGPH)</li>
                        <li>Stansted (EGSS)</li>
                        <li>Liverpool (EGGP)</li>
                    </ul>
                    @foreach($hours[0] as $icao => $hour)
                        <div class="progress" data-toggle="tooltip" title="{{ $icao }}">
                            @if($conditions[0]->isMetForUser($_account))
                                <div class="progress-bar progress-bar-success" role="progressbar" style="width: 100%" aria-valuemin="0" aria-valuemax="{{ $conditions[0]->required_hours }}">{{ round($hour,2) }} Hrs {{ '('. $icao .')' }}</div>
                            @else
                                <div class="progress-bar" role="progressbar" style="width: {{ ($hour/$conditions[0]->required_hours)*100 }}%" aria-valuemin="0" aria-valuemax="{{ $conditions[0]->required_hours }}">{{ ($hour > 0) ? (round($hour,2)).' Hrs ('. $icao .')' : '' }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Group Two Controlling</div>
                <div class="panel-body">
                    Control a total of <strong>{{ $conditions[1]->required_hours }} hours</strong> on one of the following positions within the last <strong>{{ $conditions[1]->within_months }} months.</strong>
                    <ul>
                        <li>Glasgow (EGPF)</li>
                        <li>Birmingham (EGBB)</li>
                        <li>Bristol (EGGD)</li>
                        <li>Luton (EGGW)</li>
                    </ul>
                    @foreach($hours[1] as $icao => $hour)
                        <div class="progress" data-toggle="tooltip" title="{{ $icao }}">
                            @if($conditions[1]->isMetForUser($_account))
                                <div class="progress-bar progress-bar-success" role="progressbar" style="width: 100%" aria-valuemin="0" aria-valuemax="{{ $conditions[1]->required_hours }}">{{ round($hour,2) }} Hrs {{ '('. $icao .')' }}</div>
                            @else
                                <div class="progress-bar" role="progressbar" style="width: {{ ($hour/$conditions[1]->required_hours)*100 }}%" aria-valuemin="0" aria-valuemax="{{ $conditions[1]->required_hours }}">{{ ($hour > 0) ? (round($hour,2)).' Hrs ('. $icao .')' : '' }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Group Three Controlling</div>
                <div class="panel-body">
                    Control a total of <strong>{{ $conditions[2]->required_hours }} hours</strong> on one of the following positions within the last <strong>{{ $conditions[2]->within_months }} months.</strong>
                    <ul>
                        <li>Jersey (EGJJ)</li>
                        <li>Belfast Aldergrove (EGAA)</li>
                        <li>Newcastle (EGNT)</li>
                        <li>East Midlands (EGNX)</li>
                    </ul>
                    @foreach($hours[2] as $icao => $hour)
                        <div class="progress" data-toggle="tooltip" title="{{ $icao }}">
                            @if($conditions[2]->isMetForUser($_account))
                                <div class="progress-bar progress-bar-success" role="progressbar" style="width: 100%" aria-valuemin="0" aria-valuemax="{{ $conditions[2]->required_hours }}">{{ round($hour,2) }} Hrs {{ '('. $icao .')' }}</div>
                            @else
                                <div class="progress-bar" role="progressbar" style="width: {{ ($hour/$conditions[2]->required_hours)*100 }}%"aria-valuemin="0" aria-valuemax="{{ $conditions[2]->required_hours }}">{{ ($hour > 0) ? (round($hour,2)).' Hrs ('. $icao .')' : '' }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Membership Status</div>
                <div class="panel-body">
                    @if($_account->primary_state->isDivision)
                        You are a home member of the UK and no further action is required.
                    @else
                        <p class="text-danger"><strong>You are not a home member of the UK Division. If you wish to hold a Gatwick endorsement, apply to transfer to the UK by {!! link_to_route("visiting.landing", "clicking here") !!}.</strong></p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Request Moodle Course</div>
                <div class="panel-body">
                    Once you have completed the requirements above, you will be able to press the button below to request access to the Moodle course and progress to Step 2.
                    <br><br>
                    @if($positionGroup->conditionsMetForUser($_account))
                        <a href="mailto:atc-training@vatsim.uk?Subject=Gatwick%20Endorsement%20-%20Moodle%20Request&Body=Please%20grant%20me%20access%20to%20the%20Gatwick%20Endorsement%20exam%20on%20Moodle%20as%20I%20have%20now%20met%20the%20number%20of%20hours%20required%20across%20the%20three%20groups.%0A%0AGroup%201%3A%20{{ round($hours[0]->max(),1) }}%20hours%20on%20{{ $hours[0]->keys()->first() }}%20within%20the%20last%20three%20months.%0AGroup%202%3A%20{{ round($hours[1]->max(),1) }}%20hours%20on%20{{ $hours[1]->keys()->first() }}%20within%20the%20last%20three%20months.%0AGroup%203%3A%20{{ round($hours[2]->max(),1) }}%20hours%20on%20{{ $hours[2]->keys()->first() }}%20within%20the%20last%20three%20months.%0A%0AFull%20Name%3A%20{{ $_account->name }}%0AVATSIM%20CID%3A%20{{ $_account->id }}" style="text-decoration: none;">
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
