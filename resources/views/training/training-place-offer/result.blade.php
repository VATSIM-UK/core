@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading">Training Place Offer</div>
        <div class="panel-body">

            @if($result === 'accepted')
                <div class="alert alert-success">
                    <strong>Training place accepted!</strong>
                </div>

            @elseif($result === 'declined')
                <div class="alert alert-danger">
                    <strong>Offer declined.</strong> You have been removed from the waiting list.
                    If you have any questions, please contact the ATC Training Team via the helpdesk.
                </div>
            @endif

            <a href="{{ route('mship.manage.dashboard') }}" class="btn btn-link">Return to Dashboard</a>

        </div>
    </div>
@endsection