{{-- already-responded.blade.php --}}
@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading">Training Place Offer</div>
        <div class="panel-body">
            <div class="alert alert-warning">
                You have already {{ $offer->status->label() }} this training place offer.
            </div>
            <a href="{{ route('mship.manage.dashboard') }}" class="btn btn-default">Return to Dashboard</a>
        </div>
    </div>
@endsection