<?php


use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApplicationTest extends TestCase {
    use DatabaseTransactions;

    public function it_can_create_a_new_application_for_a_user(){
        $application = null;
    }

    public function it_throws_an_exception_when_attempting_to_create_a_duplicate_application(){

    }

//    public function it_can_create_an_aerodrome(){
//        $this->expectsEvents(App\Modules\Ais\Events\AerodromeCreated::class);
//
//        $aerodrome = factory(App\Modules\Ais\Models\Aerodrome::class)->create();
//
//        $this->assertTrue($aerodrome->exists, "Ais::Aerodrome Unable to persist to database.");
//    }
}