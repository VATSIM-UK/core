<?php


use Illuminate\Foundation\Testing\DatabaseTransactions;

class AerodromeModelTest extends TestCase {
    use DatabaseTransactions;

    public function test_can_create_an_aerodrome(){
        $this->expectsEvents(App\Modules\Ais\Events\AerodromeCreated::class);

        $aerodrome = factory(App\Modules\Ais\Models\Aerodrome::class)->create();

        $this->assertTrue($aerodrome->exists, "Ais::Aerodrome Unable to persist to database.");
    }
}