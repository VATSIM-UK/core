<?php

namespace Tests\Unit\NetworkData;

use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AtcSessionModelTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake([
            \App\Events\NetworkData\AtcSessionStarted::class,
            \App\Events\NetworkData\AtcSessionEnded::class,
            \App\Events\NetworkData\AtcSessionDeleted::class,
        ]);
    }

    /** @test */
    public function itCanCreateAnAtcSession()
    {
        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(\App\Models\NetworkData\Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $this->user->networkDataAtc()->save($atcSession);

        $this->assertInstanceOf(\App\Models\NetworkData\Atc::class, $atcSession,
            'NetworkData::AtcSession not created.');
        $this->assertEquals(true, $atcSession->exists, "NetworkData::AtcSession doesn't exist.");

        Event::assertDispatched(\App\Events\NetworkData\AtcSessionStarted::class);
    }

    /** @test */
    public function itCanCreateAnAtcSessionAndMarkAsDisconnected()
    {
        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(\App\Models\NetworkData\Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $this->user->networkDataAtc()->save($atcSession);

        $currentTimestamp = \Carbon\Carbon::now();

        $atcSession->disconnectAt($currentTimestamp);

        $this->assertFalse($atcSession->is_online, 'NetworkData::AtcSession is still online.');
        $this->assertTrue($atcSession->disconnected_at->toDateTimeString() === $currentTimestamp->toDateTimeString(),
            'NetworkData::AtcSession not disconnected at current time.');

        Event::assertDispatched(\App\Events\NetworkData\AtcSessionEnded::class);
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
        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(\App\Models\NetworkData\Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $account = \App\Models\Mship\Account::factory()->create();
        $account->networkDataAtc()->save($atcSession);

        $atcSession->delete();

        Event::assertDispatched(\App\Events\NetworkData\AtcSessionDeleted::class);
    }
}
