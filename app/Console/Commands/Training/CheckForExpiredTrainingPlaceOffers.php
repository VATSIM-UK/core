<?php

namespace App\Console\Commands\Training;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Console\Command;

class CheckForExpiredTrainingPlace extends Command
{
    protected $signature = 'training:check-for-expired-training-place-offers';
    protected $description = 'Expire pending training place offers that have passed their expiry time';

    public function handle(TrainingPlaceService $service): int
    {
        $expiredOffers = TrainingPlaceOffer::getExpiredOffers(now());

        foreach ($expiredOffers as $offer) {
            $service->expireOffer($offer);
        }

        return Command::SUCCESS;
    }
}