<?php

namespace Database\Factories\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\VisitingRemoval;
use App\Services\VisitTransfer\VisitingControllerInactivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitingRemovalFactory extends Factory
{
    protected $model = VisitingRemoval::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'reason' => VisitingControllerInactivity::REASON_SIX_MONTHS,
            'removed_at' => now(),
        ];
    }
}
