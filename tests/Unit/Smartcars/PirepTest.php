<?php

namespace Tests\Unit\Smartcars;

use App\Models\Smartcars\Pirep;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PirepTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanAccessPirepThroughBid()
    {
        $pirep = factory(Pirep::class)->create();
        $pirep = $pirep->first();

        $this->assertTrue($pirep->account->contains($pirep->bid->account));
    }
}
