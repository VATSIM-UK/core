<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceAccountRelationshipTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Avoid side effects from observers during unit tests
        Event::fake();
    }

    #[Test]
    public function it_can_resolve_the_direct_account_relationship(): void
    {
        $account = Account::factory()->create();

        $trainingPlace = TrainingPlace::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->assertTrue($trainingPlace->account->is($account));
        $this->assertTrue($trainingPlace->studentAccount()->is($account));
    }
}
