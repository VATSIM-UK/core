@extends('visit-transfer.site.application._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-8">
            @include('components.html.panel_open', [
                'title' => 'Choose your Facility',
                'icon' => ['type' => 'fa', 'key' => 'question']
            ])
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    <p>
                        Choosing the right facility
                        to {{ $application->status == \App\Models\VisitTransfer\Application::TYPE_VISIT ? "visit" : "transfer to" }}
                        is crucial
                        to your application progressing quickly.
                    </p>

                    @if($application->status == \App\Models\VisitTransfer\Application::TYPE_VISIT)
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
            @include('components.html.panel_close')
        </div>

        <div class="col-md-4">
            @include('components.html.panel_open', [
                'title' => 'Facility Code',
                'icon' => ['type' => 'fa', 'key' => 'question']
            ])
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <b>Have you been given a facility code?</b>
                    </p>
                    <p>
                        Enter in the code below.
                    </p>
                    <p>
                      <label for="facility-code">Facility Code:</label>
                    <form action="{{ route('visiting.application.facility.manual.post', $application->public_id) }}"
                          method="POST" class="form-inline">
                        @csrf
                        <div class="form-group">
                          <input type="text" name="facility-code" id="facility-code">
                        </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                    </p>

                </div>

            </div>
            @include('components.html.panel_close')
        </div>
      </div>

      <div class="row">
        @foreach($facilities as $facility)
            <div class="col-md-3">
                @include('components.html.panel_open', [
                    'title' => $facility->name,
                    'icon' => ['type' => 'vuk', 'key' => 'letter-'.strtolower($facility->name[0])],
                    'attr' => ['style' => 'min-height: 220px;']
                ])
                <div class="row">
                    <div class="col-md-12">

                        <p class="text-center" style="text-align: justify; text-justify: inter-word;">
                            {{ $facility->description }}
                        </p>

                        <p class="text-center">
                            @if($facility->minimumATCQualification)
                                Minimum ATC Rating: {{ $facility->minimumATCQualification->name }}
                            @else
                                Minimum ATC Rating: None
                            @endif
                            <br>
                            @if($facility->minimumATCQualification)
                                Maximum ATC Rating: {{ $facility->maximumATCQualification->name }}
                            @else
                                Maximum ATC Rating: None
                            @endif
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
                        <form action="{{ route('visiting.application.facility.post', $application->public_id) }}"
                              method="POST">
                            @csrf

                        <p class="text-center">
                            @if($facility->training_spaces > 0 || $facility->training_spaces === null || !$facility->training_required)
                                <button type="submit" class="btn btn-primary">APPLY TO THIS FACILITY</button>
                            @else
                                <button class="btn btn-danger" disabled="disabled">NO PLACES AVAILABLE</button>
                            @endif
                        </p>

                        <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                        </form>
                    </div>

                </div>
                @include('components.html.panel_close')
            </div>
        @endforeach
    </div>
@stop
