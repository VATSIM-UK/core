@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading">Training Place Offer</div>
        <div class="panel-body">

            @if($result === 'accepted')
                <div class="alert alert-success">
                    <strong>Training place accepted!</strong> You have accepted your training place on {{ $offer->trainingPosition->position->name }} ({{ $offer->trainingPosition->position->callsign }}).
                </div>

            @elseif($result === 'declined')
                <div class="alert alert-danger">
                    <strong>Training place declined.</strong> You have declined your training place offer for {{ $offer->trainingPosition->position->name }} ({{ $offer->trainingPosition->position->callsign }}) and therefore have been removed from the waiting list.
                </div>
            @endif

            <a href="{{ route('mship.manage.dashboard') }}" class="btn btn-link">Return to Dashboard</a>

        </div>
    </div>
@endsection