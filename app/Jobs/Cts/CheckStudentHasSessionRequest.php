<?php

namespace App\Jobs\Cts;

use App\Events\Cts\StudentFailedSessionRequestCheck;
use App\Models\Cts\Member;
use App\Repositories\Cts\SessionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckStudentHasSessionRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $member;
    private $rtsId;

    /**
     * Create a new job instance.
     *
     * @param  Member  $member
     * @param  int  $rtsId
     */
    public function __construct(Member $member, int $rtsId)
    {
        $this->member = $member;
        $this->rtsId = $rtsId;
    }

    /**
     * Execute the job.
     *
     * @param  SessionRepository  $repository
     * @return void
     */
    public function handle(SessionRepository $repository)
    {
        $sessions = $repository->getSessionsForMemberByRts($this->member, $this->rtsId);

        $sessions->filter(function ($session) {
            return is_null($session->taken_time);
        });

        if ($sessions->isEmpty()) {
            event(new StudentFailedSessionRequestCheck($this->member, $this->rtsId));
        }
    }
}
