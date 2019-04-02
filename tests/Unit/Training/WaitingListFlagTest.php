<?php

namespace Tests\Unit\Training;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingListFlag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WaitingListFlagTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;
    private $flag;

    protected function setUp()
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->flag = factory(WaitingListFlag::class)->create();
        $this->waitingList->addFlag($this->flag);
    }

    /** @test */
    public function itCanBeDeleted()
    {
        $this->flag->delete();

        $this->assertFalse($this->waitingList->flags()->contains($this->flag));
    }
}
