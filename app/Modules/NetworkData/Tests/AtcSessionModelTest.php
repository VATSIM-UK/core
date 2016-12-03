<?php


use Illuminate\Foundation\Testing\DatabaseTransactions;

class AtcSessionModelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_an_atc_session()
    {
        $this->expectsEvents(App\Modules\NetworkData\Events\AtcSessionStarted::class);

        $atcSession = factory(App\Modules\NetworkData\Models\Atc::class, 'online')->create();

        $this->assertInstanceOf("App\Modules\Statistics\Models\Atc", $atcSession, 'Statistics::AtcSession not created.');
        $this->assertAttributeEquals(true, 'exists', $atcSession, "Statistics::AtcSession doesn't exist.");
    }

    public function test_can_create_an_atc_session_and_mark_as_disconnected()
    {
        $this->expectsEvents(App\Modules\NetworkData\Events\AtcSessionStarted::class);

        $atcSession = factory(App\Modules\NetworkData\Models\Atc::class, 'online')->create();

        $currentTimestamp = \Carbon\Carbon::now();
        $atcSession->disconnected_at = $currentTimestamp;

        $atcSession->save();

        $this->assertTrue(($atcSession->disconnected_at == $currentTimestamp), 'Statistics::AtcSession not disconnected.');
    }

    public function test_can_delete_an_atc_session()
    {
        $this->expectsEvents(App\Modules\NetworkData\Events\AtcsessionDeleted::class);
        $atcSession = factory(App\Modules\NetworkData\Models\Atc::class, 'online')->create();

        $atcSession->delete();

        $this->assertInstanceOf("App\Modules\Statistics\Models\Atc", $atcSession, 'Statistics::AtcSession cannot deleted an uncreated session.');
        $this->assertAttributeEquals(false, 'exists', $atcSession, 'Statistics::AtcSession not deleted.');
    }
}
