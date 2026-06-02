<?php

namespace Tests\Unit\NetworkData;

use App\Models\Cts\Session as MentoringSession;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AtcSessionModelTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            \App\Events\NetworkData\AtcSessionStarted::class,
            \App\Events\NetworkData\AtcSessionEnded::class,
            \App\Events\NetworkData\AtcSessionDeleted::class,
        ]);
    }

    #[Test]
    public function it_can_create_an_atc_session()
    {
        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $this->user->networkDataAtc()->save($atcSession);

        $this->assertInstanceOf(Atc::class, $atcSession,
            'NetworkData::AtcSession not created.');
        $this->assertEquals(true, $atcSession->exists, "NetworkData::AtcSession doesn't exist.");

        Event::assertDispatched(\App\Events\NetworkData\AtcSessionStarted::class);
    }

    #[Test]
    public function it_can_create_an_atc_session_and_mark_as_disconnected()
    {
        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $this->user->networkDataAtc()->save($atcSession);

        $currentTimestamp = \Carbon\Carbon::now();

        $atcSession->disconnectAt($currentTimestamp);

        $this->assertFalse($atcSession->is_online, 'NetworkData::AtcSession is still online.');
        $this->assertTrue($atcSession->disconnected_at->toDateTimeString() === $currentTimestamp->toDateTimeString(),
            'NetworkData::AtcSession not disconnected at current time.');

        Event::assertDispatched(\App\Events\NetworkData\AtcSessionEnded::class);
    }

    #[Test]
    public function it_updates_minutes_online_when_a_session_is_marked_as_disconnected()
    {
        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $account = \App\Models\Mship\Account::factory()->create();
        $account->networkDataAtc()->save($atcSession);

        $atcSession->connected_at = \Carbon\Carbon::now()->subMinutes(2);
        $atcSession->save();

        $atcSession->fresh()->disconnectAt($atcSession->connected_at->addMinutes(2));

        $this->assertEquals(2, $atcSession->fresh()->minutes_online,
            "NetworkData::AtcSession hasn't calculated minutes online.");
    }

    #[Test]
    public function it_triggers_an_event_when_an_atc_session_is_deleted()
    {
        $qualification = Qualification::inRandomOrder()->first();
        $atcSession = factory(Atc::class)->make();
        $atcSession->qualification_id = $qualification->id;
        $account = \App\Models\Mship\Account::factory()->create();
        $account->networkDataAtc()->save($atcSession);

        $atcSession->delete();

        Event::assertDispatched(\App\Events\NetworkData\AtcSessionDeleted::class);
    }

    #[Test]
    public function it_detects_overlap_when_atc_session_overlaps_mentoring()
    {
        $atc = factory(Atc::class)->make();
        $atc->connected_at = \Carbon\Carbon::parse('2026-03-29 18:30:00');
        $atc->disconnected_at = \Carbon\Carbon::parse('2026-03-29 19:30:00');
        $this->user->networkDataAtc()->save($atc);

        $mentoring = MentoringSession::factory()->create([
            'taken_date' => '2026-03-29',
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
            'session_done' => 1,
            'noShow' => 0,
            'cancelled_datetime' => null,
        ]);

        $this->assertTrue($atc->hasOverlappingCompletedMentoringSession(collect([$mentoring])));
    }

    #[Test]
    public function it_returns_false_when_atc_session_does_not_overlap_mentoring()
    {
        $atc = factory(Atc::class)->make();
        $atc->connected_at = \Carbon\Carbon::parse('2026-03-29 15:00:00');
        $atc->disconnected_at = \Carbon\Carbon::parse('2026-03-29 17:00:00');
        $this->user->networkDataAtc()->save($atc);

        $differentDate = MentoringSession::factory()->create([
            'taken_date' => '2026-05-10',
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
            'session_done' => 1,
            'noShow' => 0,
            'cancelled_datetime' => null,
        ]);

        $this->assertFalse($atc->hasOverlappingCompletedMentoringSession(collect([$differentDate])));
    }

    #[Test]
    public function it_returns_false_when_atc_session_has_no_disconnected_at()
    {
        $atc = factory(Atc::class)->make();
        $atc->connected_at = \Carbon\Carbon::parse('2026-03-29 18:30:00');
        $atc->disconnected_at = null;
        $this->user->networkDataAtc()->save($atc);

        $this->assertFalse($atc->hasOverlappingCompletedMentoringSession(collect()));
    }

    #[Test]
    public function it_matches_correct_mentoring_when_multiple_exist()
    {
        $atc = factory(Atc::class)->make();
        $atc->connected_at = \Carbon\Carbon::parse('2026-04-26 20:00:00');
        $atc->disconnected_at = \Carbon\Carbon::parse('2026-04-26 21:00:00');
        $this->user->networkDataAtc()->save($atc);

        $earlier = MentoringSession::factory()->create([
            'taken_date' => '2026-03-29',
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
            'session_done' => 1,
            'noShow' => 0,
        ]);

        $matching = MentoringSession::factory()->create([
            'taken_date' => '2026-04-26',
            'taken_from' => '19:30:00',
            'taken_to' => '21:30:00',
            'session_done' => 1,
            'noShow' => 0,
        ]);

        $this->assertTrue($atc->hasOverlappingCompletedMentoringSession(collect([$earlier, $matching])));
    }

    #[Test]
    public function it_returns_adjacent_positions_with_deduplication_and_exclusions(): void
    {
        $mentoringSession = MentoringSession::factory()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-03-29',
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
        ]);

        // Student on the mentored position
        factory(Atc::class)->states('offline')->create([
            'account_id' => $mentoringSession->student->cid,
            'callsign' => 'EGKK_TWR',
            'connected_at' => '2026-03-29 18:00:00',
            'disconnected_at' => '2026-03-29 20:00:00',
        ]);

        // Adjacent: EGKK_APP (two overlapping sessions - deduped to one)
        factory(Atc::class)->states('offline')->create([
            'callsign' => 'EGKK_APP',
            'connected_at' => '2026-03-29 18:00:00',
            'disconnected_at' => '2026-03-29 19:00:00',
        ]);
        factory(Atc::class)->states('offline')->create([
            'callsign' => 'EGKK_APP',
            'connected_at' => '2026-03-29 19:00:00',
            'disconnected_at' => '2026-03-29 20:00:00',
        ]);

        // Adjacent but no frequency - excluded
        factory(Atc::class)->states('offline')->create([
            'callsign' => 'EGKK_GND',
            'frequency' => null,
            'connected_at' => '2026-03-29 18:00:00',
            'disconnected_at' => '2026-03-29 20:00:00',
        ]);

        // Different aerodrome - excluded by callsign LIKE
        factory(Atc::class)->states('offline')->create([
            'callsign' => 'EGLL_APP',
            'connected_at' => '2026-03-29 18:30:00',
            'disconnected_at' => '2026-03-29 19:30:00',
        ]);

        $result = Atc::adjacentPositionsForMentoringSession($mentoringSession);

        $this->assertCount(1, $result);
        $this->assertEquals('EGKK_APP', $result->first()->callsign);
    }

    #[Test]
    public function it_excludes_atc_outside_the_session_time_range(): void
    {
        $mentoringSession = MentoringSession::factory()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-03-29',
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
        ]);

        factory(Atc::class)->states('offline')->create([
            'account_id' => $mentoringSession->student->cid,
            'callsign' => 'EGKK_TWR',
            'connected_at' => '2026-03-29 18:00:00',
            'disconnected_at' => '2026-03-29 20:00:00',
        ]);

        // Adjacent session ended before the mentoring window
        factory(Atc::class)->states('offline')->create([
            'callsign' => 'EGKK_APP',
            'connected_at' => '2026-03-29 16:00:00',
            'disconnected_at' => '2026-03-29 17:00:00',
        ]);

        // Adjacent session started after the mentoring window
        factory(Atc::class)->states('offline')->create([
            'callsign' => 'EGKK_GND',
            'connected_at' => '2026-03-29 21:00:00',
            'disconnected_at' => '2026-03-29 22:00:00',
        ]);

        $result = Atc::adjacentPositionsForMentoringSession($mentoringSession);

        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_returns_empty_when_no_valid_student_session(): void
    {
        $mentoringSession = MentoringSession::factory()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-03-29',
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
        ]);

        // No ATC sessions at all
        $result = Atc::adjacentPositionsForMentoringSession($mentoringSession);
        $this->assertCount(0, $result);

        // Adjacent ATC exists but no student session - empty
        factory(Atc::class)->states('offline')->create([
            'callsign' => 'EGKK_APP',
            'connected_at' => '2026-03-29 18:30:00',
            'disconnected_at' => '2026-03-29 19:30:00',
        ]);

        $result = Atc::adjacentPositionsForMentoringSession($mentoringSession);
        $this->assertCount(0, $result);

        // Student session exists but without frequency - treated as not on network
        factory(Atc::class)->states('offline')->create([
            'account_id' => $mentoringSession->student->cid,
            'callsign' => 'EGKK_TWR',
            'frequency' => null,
            'connected_at' => '2026-03-29 18:00:00',
            'disconnected_at' => '2026-03-29 20:00:00',
        ]);

        $result = Atc::adjacentPositionsForMentoringSession($mentoringSession);
        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_excludes_relief_sessions_from_the_student_being_mentored(): void
    {
        $mentoringSession = MentoringSession::factory()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-03-29',
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
        ]);

        // Adjacent session from the same student on relief - should be ignored
        factory(Atc::class)->states('offline')->create([
            'account_id' => $mentoringSession->student->cid,
            'callsign' => 'EGKK__TWR',
            'connected_at' => '2026-03-29 18:30:00',
            'disconnected_at' => '2026-03-29 19:30:00',
        ]);

        $result = Atc::adjacentPositionsForMentoringSession($mentoringSession);
        $this->assertCount(0, $result);
    }
}
