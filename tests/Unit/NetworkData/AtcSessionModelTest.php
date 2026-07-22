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

    // ─── adjacentPositionsForMentoringSession ─────────────────────────────

    private function createSession(string $position, string $from, string $to): MentoringSession
    {
        return MentoringSession::factory()->create([
            'position' => $position,
            'taken_date' => '2026-03-29',
            'taken_from' => $from,
            'taken_to' => $to,
        ]);
    }

    private function createStudentAtc(MentoringSession $session): void
    {
        factory(Atc::class)->states('offline')->create([
            'account_id' => $session->student->cid,
            'callsign' => $session->position,
            'connected_at' => "2026-03-29 {$session->taken_from}",
            'disconnected_at' => "2026-03-29 {$session->taken_to}",
        ]);
    }

    private function createAdjacentAtc(string $callsign, string $connectedAt, ?string $disconnectedAt = null): void
    {
        factory(Atc::class)->states('offline')->create([
            'callsign' => $callsign,
            'connected_at' => "2026-03-29 {$connectedAt}",
            'disconnected_at' => $disconnectedAt ? "2026-03-29 {$disconnectedAt}" : null,
        ]);
    }

    #[Test]
    public function it_returns_adjacent_positions_with_sufficient_overlap(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');
        $this->createStudentAtc($session);
        $this->createAdjacentAtc('EGKK_APP', '18:00:00', '20:00:00');
        $this->createAdjacentAtc('EGKK_GND', '19:00:00', '19:45:00');

        $result = Atc::adjacentPositionsForMentoringSession($session);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('callsign', 'EGKK_APP'));
        $this->assertTrue($result->contains('callsign', 'EGKK_GND'));
    }

    #[Test]
    public function it_excludes_adjacent_atc_with_less_than_15_minutes_overlap(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');
        $this->createStudentAtc($session);
        $this->createAdjacentAtc('EGKK_GND', '19:50:00', '20:00:00'); // 10 min overlap → excluded

        $result = Atc::adjacentPositionsForMentoringSession($session);
        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_includes_still_online_adjacent_atc_with_sufficient_overlap(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');
        $this->createStudentAtc($session);
        $this->createAdjacentAtc('EGKK_APP', '19:00:00', null); // 60 min overlap, still online

        $result = Atc::adjacentPositionsForMentoringSession($session);
        $this->assertCount(1, $result);
    }

    #[Test]
    public function it_deduplicates_adjacent_positions_with_multiple_sessions(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');
        $this->createStudentAtc($session);
        $this->createAdjacentAtc('EGKK_APP', '18:00:00', '19:00:00');
        $this->createAdjacentAtc('EGKK_APP', '19:00:00', '20:00:00');

        $result = Atc::adjacentPositionsForMentoringSession($session);

        $this->assertCount(1, $result);
        $this->assertEquals('EGKK_APP', $result->first()->callsign);
    }

    #[Test]
    public function it_excludes_positions_without_frequency(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');
        $this->createStudentAtc($session);
        factory(Atc::class)->states('offline')->create([
            'callsign' => 'EGKK_GND',
            'frequency' => null,
            'connected_at' => '2026-03-29 18:00:00',
            'disconnected_at' => '2026-03-29 20:00:00',
        ]);

        $result = Atc::adjacentPositionsForMentoringSession($session);
        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_excludes_positions_at_different_aerodromes(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');
        $this->createStudentAtc($session);
        $this->createAdjacentAtc('EGLL_APP', '18:30:00', '19:30:00');

        $result = Atc::adjacentPositionsForMentoringSession($session);
        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_excludes_atc_outside_the_session_time_range(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');
        $this->createStudentAtc($session);
        $this->createAdjacentAtc('EGKK_APP', '16:00:00', '17:00:00'); // before
        $this->createAdjacentAtc('EGKK_GND', '21:00:00', '22:00:00'); // after

        $result = Atc::adjacentPositionsForMentoringSession($session);
        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_returns_empty_when_no_valid_student_session(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');

        // No student network session at all
        $this->assertCount(0, Atc::adjacentPositionsForMentoringSession($session));

        // Student session without frequency (e.g. sweatbox)
        factory(Atc::class)->states('offline')->create([
            'account_id' => $session->student->cid,
            'callsign' => 'EGKK_TWR',
            'frequency' => null,
            'connected_at' => '2026-03-29 18:00:00',
            'disconnected_at' => '2026-03-29 20:00:00',
        ]);
        $this->createAdjacentAtc('EGKK_APP', '18:30:00', '19:30:00');

        $this->assertCount(0, Atc::adjacentPositionsForMentoringSession($session));
    }

    #[Test]
    public function it_excludes_same_student_on_relief_positions(): void
    {
        $session = $this->createSession('EGKK_TWR', '18:00:00', '20:00:00');
        $this->createStudentAtc($session);
        factory(Atc::class)->states('offline')->create([
            'account_id' => $session->student->cid,
            'callsign' => 'EGKK__TWR',
            'connected_at' => '2026-03-29 18:30:00',
            'disconnected_at' => '2026-03-29 19:30:00',
        ]);

        $result = Atc::adjacentPositionsForMentoringSession($session);
        $this->assertCount(0, $result);
    }
}
