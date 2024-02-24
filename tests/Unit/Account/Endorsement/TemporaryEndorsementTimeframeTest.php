<?php

namespace Tests\Unit\Account\Endorsement;

use App\Models\Atc\Position;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use Tests\TestCase;

class TemporaryEndorsementTimeframeTest extends TestCase
{
    public function test_can_detect_time_already_temporarily_endorsed_on_position()
    {
        $position = Position::factory()->create();
        $account = Account::factory()->create();

        // create a temporary endorsement that has expired
        Endorsement::create([
            'account_id' => $account->id,
            'endorsable_id' => $position->id,
            'endorsable_type' => Position::class,
            'created_at' => now()->subDays(8),
            'expires_at' => now()->subDays(1),
            'created_by' => $this->privacc->id,
        ]);

        // create endorsement that is active to ensure it is included
        Endorsement::create([
            'account_id' => $account->id,
            'endorsable_id' => $position->id,
            'endorsable_type' => Position::class,
            'created_at' => now(),
            'expires_at' => now()->addDays(2),
            'created_by' => $this->privacc->id,
        ]);

        $result = $account->daysSpentTemporarilyEndorsedOn($position);

        $this->assertEquals(9, $result);
    }

    public function test_detects_when_no_days_on_position()
    {
        $position = Position::factory()->create();
        $account = Account::factory()->create();

        $result = $account->daysSpentTemporarilyEndorsedOn($position);

        $this->assertEquals(0, $result);
    }
}
