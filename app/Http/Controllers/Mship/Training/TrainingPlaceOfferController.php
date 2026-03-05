<?php

namespace App\Http\Controllers\Mship\Training;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Http\Request;

class TrainingPlaceOfferController extends \App\Http\Controllers\BaseController
{
    public function show(string $token)
    {
        $offer = TrainingPlaceOffer::where('token', $token)->first();

        if (! $offer) {
            abort(404, 'This offer does not exist.');
        }

        if ($offer->expires_at->isPast()) {
            return view('training.training-place-offer.expired', compact('offer'));
        }

        if ($offer->status !== TrainingPlaceOfferStatus::Pending) {
            return view('training.training-place-offer.already-responded', compact('offer'));
        }

        if ($offer->waitingListAccount->account->id !== auth()->id()) {
            abort(403);
        }

        return view('training.training-place-offer.show', compact('offer'));
    }

    public function respond(string $token, Request $request, TrainingPlaceService $service)
    {
        $offer = TrainingPlaceOffer::where('token', $token)->firstOrFail();

        if ($offer->expires_at->isPast()) {
            return redirect()->route('mship.manage.dashboard')
                ->with('error', 'This offer has expired.');
        }

        if ($offer->status !== TrainingPlaceOfferStatus::Pending) {
            return redirect()->route('mship.manage.dashboard')
                ->with('error', 'This offer has already been responded to.');
        }

        if ($offer->waitingListAccount->account->id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'response' => ['required', 'in:accepted,declined'],
            'decline_reason' => ['required_if:response,declined', 'nullable', 'string', 'min:10', 'max:1000'],
        ]);

        if ($request->response === 'accepted') {
            $service->acceptOffer($offer);
            return redirect()->route('mship.manage.dashboard')
                ->with('success', 'You have accepted your training place offer.');
        }

        $service->declineOffer($offer, $request->decline_reason);
        return redirect()->route('mship.manage.dashboard')
            ->with('info', 'You have declined your training place offer.');
    }
}