<?php

namespace App\Services\Training;

use App\Events\Training\TrainingPlaceOffered;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPlace\TrainingPosition;
use App\Services\BaseService;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class OfferTrainingPlace implements BaseService
{
    private $trainingPosition;
    private $account;
    private $offeringAccount;
    private $expiryHours;

    public function __construct(TrainingPosition $trainingPosition, Account $account, Account $offeringAccount, int $expiryHours = 72)
    {
        $this->trainingPosition = $trainingPosition;
        $this->account = $account;
        $this->offeringAccount = $offeringAccount;
        $this->expiryHours = $expiryHours;
    }

    public function handle()
    {
        $offer = TrainingPlaceOffer::create([
            'account_id' => $this->account->id,
            'offer_id' => Uuid::uuid4(),
            'offered_by' => $this->offeringAccount->id,
            'expires_at' => Carbon::now()->addHours($this->expiryHours),
            'training_position_id' => $this->trainingPosition->id,
        ]);

        event(new TrainingPlaceOffered($offer));
    }
}
