<?php

namespace Tests\Unit\Cts;

use Carbon\Carbon;
use \Mockery as m;
use Tests\UnitTestCase;
use App\Models\Cts\Session as SessionTable;
use App\Repositories\Cts\SessionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SessionRepositoryTest extends UnitTestCase
{
    use RefreshDatabase;

    /** @var \App\Repositories\Cts\SessionRepository */
    private $subjectUnderTest;

    protected function setUp()
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(SessionRepository::class);
    }

    /**
     * @test
     *
     * @group Community
     */
    public function can_return_unique_mentor_ids_in_last_28_days()
    {
        $defaultBecauseDefault = [
            'position'          => 'EGHQ_APP',
            'progress_sheet_id' => 0,
        ];

        SessionTable::unguard();

        $session1 = SessionTable::create([
                'mentor_id'    => 980234,
                'taken_date'   => Carbon::now()->subDay()->toDateString(),
                'session_done' => 1,
                'noShow'       => 0,
            ] + $defaultBecauseDefault);

        $session2 = SessionTable::create([
                'mentor_id'    => 980234,
                'taken_date'   => Carbon::now()->subDays(14)->toDateString(),
                'session_done' => 1,
                'noShow'       => 0,
            ] + $defaultBecauseDefault);

        $session3 = SessionTable::create([
                'mentor_id'    => 1234567,
                'taken_date'   => Carbon::now()->subDays(28)->toDateString(),
                'session_done' => 1,
                'noShow'       => 0,
            ] + $defaultBecauseDefault);

        SessionTable::reguard();

        $mentorIds = $this->subjectUnderTest->mentorIdsForSessionsInLast28Days();

        $this->assertInternalType('array', $mentorIds);

        $this->assertEquals([
            980234,
            1234567,
        ], $mentorIds);
    }
}
