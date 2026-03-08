<?php

namespace App\Services\Training;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Notifications\Training\TrainingPlaceOffered;
use App\Notifications\Training\TrainingPlaceOfferRescinded;
use App\Notifications\Training\TrainingPlaceOfferRescindedAndRemoved;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrainingPlaceOfferService
{
    public function offerTrainingPlace(WaitingListAccount $waitingListAccount, TrainingPosition $trainingPosition)
    {
        DB::transaction(function () use ($waitingListAccount, $trainingPosition): void {
            $trainingPlaceOffer = TrainingPlaceOffer::create([
                'waiting_list_account_id' => $waitingListAccount->id,
                'training_position_id' => $trainingPosition->id,
                'status' => TrainingPlaceOfferStatus::Pending->value,
                'token' => self::generateToken(),
                'expires_at' => now()->addHours(84)->endOfHour(), // 3.5 days
            ]);
            $waitingListAccount->account->notify(new TrainingPlaceOffered($trainingPlaceOffer));
        });
    }

    public function acceptOffer(TrainingPlaceOffer $offer): void
    {
        DB::transaction(function () use ($offer): void {
            $offer->update([
                'status' => TrainingPlaceOfferStatus::Accepted->value,
                'response_at' => now(),
            ]);

            $this->createTrainingPlace($offer->waitingListAccount, $offer->trainingPosition);
        });

    }

    public function declineOffer(TrainingPlaceOffer $trainingPlaceOffer): void
    {
        DB::transaction(function () use ($trainingPlaceOffer): void {
            $trainingPlaceOffer->update([
                'status' => TrainingPlaceOfferStatus::Declined->value,
                'response_at' => now(),
            ]);

            $removal = new Removal(RemovalReason::DeclinedTrainingPlaceOffer, auth()->id());
            $this->removeFromWaitingList($trainingPlaceOffer, $removal);
        });
    }

    public function rescindOffer(TrainingPlaceOffer $trainingPlaceOffer, string $reason): void
    {
        $trainingPlaceOffer->update([
            'status' => TrainingPlaceOfferStatus::Rescinded->value,
        ]);

        $trainingPlaceOffer->waitingListAccount->account->notify(new TrainingPlaceOfferRescinded($trainingPlaceOffer, $reason));
    }

    public function rescindOfferAndRemove(TrainingPlaceOffer $trainingPlaceOffer, string $reason): void
    {
        $trainingPlaceOffer->update([
            'status' => TrainingPlaceOfferStatus::Rescinded->value,
        ]);

        $trainingPlaceOffer->waitingListAccount->account->notify(new TrainingPlaceOfferRescindedAndRemoved($trainingPlaceOffer, $reason));

        $removal = new Removal(RemovalReason::TrainingPlaceOfferRescinded, auth()->id());
        $this->removeFromWaitingList($trainingPlaceOffer, $removal);
    }

    public function expireOffer(TrainingPlaceOffer $trainingPlaceOffer): void
    {
        $trainingPlaceOffer->update([
            'status' => TrainingPlaceOfferStatus::Expired->value,
        ]);

        $removal = new Removal(RemovalReason::TrainingPlaceOfferExpired, auth()->id());
        $this->removeFromWaitingList($trainingPlaceOffer, $removal);
    }

    public function removeFromWaitingList(TrainingPlaceOffer $trainingPlaceOffer, Removal $removal)
    {
        $trainingPlaceOffer->waitingListAccount->waitingList->removeFromWaitingList($trainingPlaceOffer->waitingListAccount->account, $removal);
    }

    public function createTrainingPlace(WaitingListAccount $waitingListAccount, TrainingPosition $trainingPosition)
    {
        return app(TrainingPlaceService::class)->createManualTrainingPlace($waitingListAccount, $trainingPosition);
    }

    private static function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (TrainingPlaceOffer::where('token', $token)->exists());

        return $token;
    }
}
