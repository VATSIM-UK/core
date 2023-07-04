<?php

namespace Tests\Unit\NetworkData;

use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AtcSessionModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanCreateAnAtcSession()
    {
        $this->expectsEvents(\App\Events\NetworkData\AtcSessionStarted::class);

        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(\App\Models\NetworkData\Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $this->user->networkDataAtc()->save($atcSession);

        $this->assertInstanceOf(\App\Models\NetworkData\Atc::class, $atcSession,
            'NetworkData::AtcSession not created.');
        $this->assertEquals(true, $atcSession->exists, "NetworkData::AtcSession doesn't exist.");
    }

    /** @test */
    public function itCanCreateAnAtcSessionAndMarkAsDisconnected()
    {
        $this->expectsEvents(\App\Events\NetworkData\AtcSessionEnded::class);

        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(\App\Models\NetworkData\Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $this->user->networkDataAtc()->save($atcSession);

        $currentTimestamp = \Carbon\Carbon::now();

        $atcSession->disconnectAt($currentTimestamp);

        $this->assertFalse($atcSession->is_online, 'NetworkData::AtcSession is still online.');
        $this->assertTrue($atcSession->disconnected_at->toDateTimeString() === $currentTimestamp->toDateTimeString(),
            'NetworkData::AtcSession not disconnected at current time.');
    }

    /** @test */
    public function itUpdatesMinutesOnlineWhenASessionIsMarkedAsDisconnected()
    {
        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(\App\Models\NetworkData\Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $account = \App\Models\Mship\Account::factory()->create();
        $account->networkDataAtc()->save($atcSession);

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

        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(\App\Models\NetworkData\Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $account = \App\Models\Mship\Account::factory()->create();
        $account->networkDataAtc()->save($atcSession);

        $atcSession->delete();
    }
}
