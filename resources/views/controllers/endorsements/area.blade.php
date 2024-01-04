@extends('layout')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h3>C1 Training Place - Hour Checker</h3>
        <p>You must have controlled at least 12 hours on UK ATC positions in the three months before a place is offered.</p>
        <p>When a training place becomes available for a particular sector group, the first eligible student who meets the requirements for that sector group will be offered the place.
        You will only be offered a place for a sector group for which you meet the requirements. You do not need to notify us when you meet the requirements.</p>
        <p>Below you can view your progress towards the required criteria for each enroute sector group.</p>
    </div>
</div>
<div class="row">
    @foreach ($positionGroups as $positionGroup)
        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fas fa-headset"></i>&nbsp;{{ $positionGroup['name'] }}
                </div>
                <div class="panel-body">
                    <header>
                        <h4>Required Hours</h4>
                        <span>The following hours are required on the corresponding positions</span>
                        <ul>
                            @foreach($positionGroup['conditions'] as $condition)
                                <li>{{ $condition['position'] }} - {{ $condition['required_hours'] }} hours within the last {{ $condition['within_months'] }} months.</li>
                            @endforeach
                        </ul>
                    </header>
                    <main>
                        <h4>Your Progress</h4>

                        @foreach($positionGroup['conditions'] as $condition)
                        {{ $condition['position'] }}
                        <div class="progress">
                            @if ($condition['complete'])
                                <div class="progress-bar progress-bar-success" role="progressbar" style="width: 100%">{{ $condition['progress']}} hours</div>
                            @else
                                <div class="progress-bar" role="progressbar" style="width: {{ ($condition['progress'] / $condition['required_hours']) * 100 }}%" aria-valuemin="0" aria-valuemax="{{ $condition['required_hours'] }}">
                                    {{ $condition['progress'] }} hours
                                </div>
                            @endif
                        </div>
                         @endforeach

                    </main>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
