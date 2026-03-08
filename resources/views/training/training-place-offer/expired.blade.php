@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading">Training Place Offer</div>
        <div class="panel-body">
            <div class="alert alert-danger">
                <strong>Offer expired.</strong> This training place offer expired at
                <strong>{{ $offer->expires_at->format('H:i') }}Z on {{ $offer->expires_at->format('d/m/Y') }}</strong>
                and is no longer available.

                <p>If you believe this is a mistake or have any questions, please contact the ATC Training team via the helpdesk.</p>
            </div>

            <a href="{{ route('mship.manage.dashboard') }}" class="btn btn-link">Return to Dashboard</a>
        </div>
    </div>
@endsection