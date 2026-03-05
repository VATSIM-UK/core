@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading">Training Place Offer</div>
        <div class="panel-body">
            <div class="alert alert-danger">
                This training place offer expired on {{ $offer->expires_at->format('d/m/Y H:i') }} and is no longer available.
            </div>
            <p>If you believe this is a mistake, please contact the training team via the helpdesk.</p>
            <a href="{{ route('mship.manage.dashboard') }}" class="btn btn-default">Return to Dashboard</a>
        </div>
    </div>
@endsection