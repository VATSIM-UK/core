<?php

namespace Tests\Unit\Cts\Jobs;

use App\Events\Cts\StudentFailedSessionRequestCheck;
use App\Jobs\Cts\CheckStudentHasSessionRequest;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CheckStudentHasSessionRequestTest extends TestCase
{
    use DatabaseTransactions;

    private $member;
    private $rts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = factory(Member::class)->create();
        $this->rts = 1;
    }

    /** @test */
    public function itRaisesAnEventToSignifyCheckFailed()
    {
        Event::fake();

        // create a session which has already occurred.
        factory(Session::class)->create(['taken_time' => now()->subDay(), 'rts_id' => $this->rts]);

        CheckStudentHasSessionRequest::dispatchNow($this->member, $this->rts);

        Event::assertDispatched(function (StudentFailedSessionRequestCheck $event) {
            return $event->account->id === $this->member->id;
        });
    }

    /** @test */
    public function itDoesNotFireEventIfStudentHasSessionRequest()
    {
        Event::fake();

        // session request is indicated by lack of taken_time attribute
        factory(Session::class)->create(['taken_time' => null, 'rts_id' => $this->rts]);

        Event::assertNotDispatched(StudentFailedSessionRequestCheck::class);
    }

    /** @test */
    public function itPerformsACheckForEachActiveMemberInAnRts()
    {

    }

}
