<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AtcSessionModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_create_an_atc_session()
    {
        $this->expectsEvents(App\Modules\NetworkData\Events\AtcSessionStarted::class);

        $atcSession = factory(App\Modules\NetworkData\Models\Atc::class, 'online')->create();

        $this->assertInstanceOf(App\Modules\NetworkData\Models\Atc::class, $atcSession,
            'NetworkData::AtcSession not created.');
        $this->assertAttributeEquals(true, 'exists', $atcSession, "NetworkData::AtcSession doesn't exist.");
    }

    /** @test */
    public function it_can_create_an_atc_session_and_mark_as_disconnected()
    {
        $this->expectsEvents(App\Modules\NetworkData\Events\AtcSessionEnded::class);

        $atcSession = factory(App\Modules\NetworkData\Models\Atc::class, 'online')->create();

        $currentTimestamp = \Carbon\Carbon::now();

        $atcSession->disconnectAt($currentTimestamp);

        $this->assertFalse($atcSession->is_online, 'NetworkData::AtcSession not disconnected.');
        $this->assertTrue(($atcSession->disconnected_at == $currentTimestamp),
            'NetworkData::AtcSession not disconnected.');
    }

    /** @test */
    public function it_updates_minutes_online_when_a_session_is_marked_as_disconnected()
    {
        $atcSession = factory(App\Modules\NetworkData\Models\Atc::class, 'online')->create();

        $atcSession->connected_at = \Carbon\Carbon::now()->subMinutes(2);
        $atcSession->save();

        $atcSession->fresh()->disconnectAt($atcSession->connected_at->addMinutes(2));

        $this->assertEquals(2, $atcSession->fresh()->minutes_online,
            "NetworkData::AtcSession hasn't calculated minutes online.");
    }

    /** @test */
    public function it_triggers_an_event_when_an_atc_session_is_deleted()
    {
        $this->expectsEvents(App\Modules\NetworkData\Events\AtcSessionDeleted::class);

        $atcSession = factory(App\Modules\NetworkData\Models\Atc::class, 'online')->create();

        $atcSession->delete();
    }
}
