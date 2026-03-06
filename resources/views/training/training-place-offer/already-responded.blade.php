@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading">Training Place Offer</div>
        <div class="panel-body">

            @if($offer->status === \App\Enums\TrainingPlaceOfferStatus::Accepted)
                <div class="alert alert-success">
                    <strong>Already accepted.</strong> You have already accepted this training place offer.
                    If you have any questions, please contact the ATC Training team via the helpdesk.
                </div>

            @elseif($offer->status === \App\Enums\TrainingPlaceOfferStatus::Declined)
                <div class="alert alert-danger">
                    <strong>Already declined.</strong> You have already declined this training place offer
                    and have been removed from the waiting list.
                    If you believe this was a mistake, please contact the ATC Training team via the helpdesk.
                </div>

            @elseif($offer->status === \App\Enums\TrainingPlaceOfferStatus::Rescinded)
                <div class="alert alert-danger">
                    <strong>Offer rescinded.</strong> This training place offer has been rescinded by the training team.
                    If you have any questions, please contact the ATC Training team via the helpdesk.
                </div>

            <a href="{{ route('mship.manage.dashboard') }}" class="btn btn-link">Return to Dashboard</a>
        </div>
    </div>
@endsection