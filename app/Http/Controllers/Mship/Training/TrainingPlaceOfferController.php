<?php

namespace App\Http\Controllers\Mship\Training;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Services\Training\TrainingPlaceOfferService;

class TrainingPlaceOfferController extends \App\Http\Controllers\BaseController
{
    public function accept(string $token, TrainingPlaceOfferService $service)
    {
        $offer = $this->findOffer($token);

        if ($offer->waitingListAccount->account->id !== auth()->id()) {
            abort(403);
        }

        if ($this->isExpired($offer)) {
            return $this->expired($offer);
        }

        if ($offer->status !== TrainingPlaceOfferStatus::Pending) {
            return $this->alreadyResponded($offer);
        }

        $service->acceptOffer($offer);

        return view('training.training-place-offer.result', ['result' => 'accepted']);
    }

    public function decline(string $token, TrainingPlaceOfferService $service)
    {
        $offer = $this->findOffer($token);

        if ($offer->waitingListAccount->account->id !== auth()->id()) {
            abort(403);
        }

        if ($this->isExpired($offer)) {
            return $this->expired($offer);
        }

        if ($offer->status !== TrainingPlaceOfferStatus::Pending) {
            return $this->alreadyResponded($offer);
        }

        $service->declineOffer($offer);

        return view('training.training-place-offer.result', ['result' => 'declined']);
    }

    private function findOffer(string $token): TrainingPlaceOffer
    {
        return TrainingPlaceOffer::with([
            'waitingListAccount' => fn ($q) => $q->withTrashed(),
            'waitingListAccount.account',
        ])->where('token', $token)->firstOrFail();
    }

    private function isExpired(TrainingPlaceOffer $offer): bool
    {
        return $offer->status === TrainingPlaceOfferStatus::Expired || $offer->expires_at->isPast();
    }

    private function alreadyResponded(TrainingPlaceOffer $offer)
    {
        return view('training.training-place-offer.already-responded', compact('offer'));
    }

    private function expired(TrainingPlaceOffer $offer)
    {
        return view('training.training-place-offer.expired', compact('offer'));
    }
}