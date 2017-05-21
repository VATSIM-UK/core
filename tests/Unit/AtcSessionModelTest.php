<?php

namespace Tests\Unit;

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AtcSessionModelTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanCreateAnAtcSession()
    {
        $this->expectsEvents(\App\Events\NetworkData\AtcSessionStarted::class);

        $atcSession = factory(\App\Models\NetworkData\Atc::class, 'online')->create();

        $this->assertInstanceOf(\App\Models\NetworkData\Atc::class, $atcSession,
            'NetworkData::AtcSession not created.');
        $this->assertAttributeEquals(true, 'exists', $atcSession, "NetworkData::AtcSession doesn't exist.");
    }

    /** @test */
    public function itCanCreateAnAtcSessionAndMarkAsDisconnected()
    {
        $this->expectsEvents(\App\Events\NetworkData\AtcSessionEnded::class);

        $atcSession = factory(\App\Models\NetworkData\Atc::class, 'online')->create();

        $currentTimestamp = \Carbon\Carbon::now();

        $atcSession->disconnectAt($currentTimestamp);

        $this->assertFalse($atcSession->is_online, 'NetworkData::AtcSession is still online.');
        $this->assertTrue($atcSession->disconnected_at->toDateTimeString() === $currentTimestamp->toDateTimeString(),
            'NetworkData::AtcSession not disconnected at current time.');
    }

    /** @test */
    public function itUpdatesMinutesOnlineWhenASessionIsMarkedAsDisconnected()
    {
        $atcSession = factory(\App\Models\NetworkData\Atc::class, 'online')->create();

        $atcSession->connected_at = \Carbon\Carbon::now()->subMinutes(2);
        $atcSession->save();

        $atcSession->fresh()->disconnectAt($atcSession->connected_at->addMinutes(2));

        $this->assertEquals(2, $atcSession->fresh()->minutes_online,
            "NetworkData::AtcSession hasn't calculated minutes online.");
    }

    /** @test */
    public function itTriggersAnEventWhenAnAtcSessionIsDeleted()
    {
        $this->expectsEvents(\App\Events\NetworkData\AtcSessionDeleted::class);

        $atcSession = factory(\App\Models\NetworkData\Atc::class, 'online')->create();

        $atcSession->delete();
    }
}
