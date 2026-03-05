@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading">Training Place Offer</div>
        <div class="panel-body">
            <div class="alert alert-danger">
                This training place offer expired on <strong>{{ $offer->expires_at->format('d/m/Y H:i') }}</strong> and is no longer available.

                <p>If you believe this is a mistake, please contact the training team via the helpdesk.</p>
            </div>
            
            <a href="{{ route('mship.manage.dashboard') }}" class="btn btn-link">Return to Dashboard</a>
        </div>
    </div>
@endsection