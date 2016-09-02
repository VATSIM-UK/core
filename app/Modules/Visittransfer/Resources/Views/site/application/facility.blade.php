@extends('visittransfer::site.application._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            {!! HTML::panelOpen("Choose your Facility", ["type" => "fa", "key" => "question"]) !!}
            <div class="row">
                <div class="col-md-6 col-md-offset-3">

                    <p>
                        Choosing the right facility
                        to {{ $application->status == \App\Modules\Visittransfer\Models\Application::TYPE_VISIT ? "visit" : "transfer to" }}
                        is crucial
                        to your application progressing quickly.
                    </p>

                    @if($application->status == \App\Modules\Visittransfer\Models\Application::TYPE_VISIT)
                        <p>
                            You can choose from all available facilities below.
                        <ul>
                            <li>Should you apply to a facility where <span class="label label-warning" id="labelTrainingHelp">TRAINING IS REQUIRED</span>
                                your visiting status can only be fully granted once your induction training has been
                                completed.
                            </li>
                            <li>When applying to a facility labelled as
                                <span class="label label-success" id="labelNoTrainingHelp">NO TRAINING REQUIRED</span>, your visitor status will
                                be automatically granted
                                once staff have accepted it.
                            </li>
                        </ul>
                        </p>
                    @endif

                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>

        @foreach($facilities as $facility)
            <div class="col-md-3">
                {!! HTML::panelOpen($facility->name, ["type" => "vuk", "key" => "letter-".strtolower($facility->name[0])], ["style" => "min-height: 220px;"]) !!}
                <div class="row">
                    <div class="col-md-12">

                        <p class="text-center" style="text-align: justify; text-justify: inter-word;">
                            {{ $facility->description }}
                        </p>
                        <p class="text-center">
                            @if($facility->training_required)
                                <span class="label label-warning">TRAINING IS REQUIRED</span>
                                <br/>
                                PLACES AVAILABLE: {!! ($facility->training_spaces === null ? "&infin;" : $facility->training_spaces) !!}
                            @else
                                <span class="label label-success">NO TRAINING REQUIRED</span>
                            @endif
                        </p>
                        {!! Form::open(["route" => ["visiting.application.facility.post", $application->public_id], "method" => "POST"]) !!}

                        <p class="text-center">
                            @if($facility->training_spaces > 0 || $facility->training_spaces === null || !$facility->training_required)
                                {!! Button::primary("APPLY TO THIS FACILITY")->submit() !!}
                            @else
                                {!! Button::danger("NO PLACES AVAILABLE")->disable() !!}
                            @endif
                        </p>

                        {!! Form::hidden("facility_id", $facility->id) !!}
                        {!! Form::close() !!}
                    </div>

                </div>
                {!! HTML::panelClose() !!}
            </div>
        @endforeach
    </div>
@stop