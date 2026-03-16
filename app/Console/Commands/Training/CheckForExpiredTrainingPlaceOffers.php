<?php

namespace App\Console\Commands\Training;

use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Services\Training\TrainingPlaceOfferService;
use Illuminate\Console\Command;

class CheckForExpiredTrainingPlaceOffers extends Command
{
    protected $signature = 'training:check-for-expired-training-place-offers';

    protected $description = 'Expire pending training place offers that have passed their expiry time';

    public function handle(TrainingPlaceOfferService $service): int
    {
        $expiredOffers = TrainingPlaceOffer::getExpiredOffers(now());

        foreach ($expiredOffers as $offer) {
            $service->expireOffer($offer);
        }

        return Command::SUCCESS;
    }
}
