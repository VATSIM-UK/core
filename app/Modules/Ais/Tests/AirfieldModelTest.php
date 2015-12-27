<?php


use Illuminate\Foundation\Testing\DatabaseTransactions;

class AirfieldModelTest extends TestCase {
    use DatabaseTransactions;

    public function test_can_create_an_airfield(){
        $atcSession = factory(App\Modules\Statistics\Models\Atc::class, "online")->create();

        $this->assertInstanceOf("App\Modules\Statistics\Models\Atc", $atcSession, "Statistics::AtcSession not created.");
        $this->assertAttributeEquals(true, "exists", $atcSession, "Statistics::AtcSession doesn't exist.");
    }
}