<?php

namespace Tests\Unit\Cts;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Repositories\Cts\SessionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SessionsRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = resolve(SessionRepository::class);
    }

    /** @test */
    public function itCanReturnSessionsGivenAnRTS()
    {
        $rts = 18;
        $session = factory(Session::class)->create(['rts_id' => $rts]);
        $excludedSession = factory(Session::class)->create(['rts_id' => $rts + 1]);

        $result = $this->repository->getSessionsByRts($rts);

        $this->assertTrue($result->contains($session));
        $this->assertFalse($result->contains($excludedSession));
    }

    /** @test */
    public function itCanReturnSessionsForAMemberWithinAnRTS()
    {
        $rts = 1;
        $student = factory(Member::class)->create();
        $session = factory(Session::class)->create(['rts_id' => $rts, 'student_id' => $student->id]);

        // represents session from student in another group
        $outOfScopeSession = factory(Session::class)->create(['rts_id' => $rts + 1, 'student_id' => $student->id]);

        $result = $this->repository->getSessionsForMemberByRts($student, $rts);

        $this->assertTrue($result->contains($session));
        $this->assertFalse($result->contains($outOfScopeSession));
    }
}
