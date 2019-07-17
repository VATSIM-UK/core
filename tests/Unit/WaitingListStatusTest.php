<?php

namespace Tests\Unit;

use App\Models\Training\WaitingListStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListStatusTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingListStatus;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingListStatus = factory(WaitingListStatus::class)->create();
    }

    /** @test * */
    public function itDefaultsToActive()
    {
        $this->assertEquals(1, $this->waitingListStatus->default()->id);
    }
}
