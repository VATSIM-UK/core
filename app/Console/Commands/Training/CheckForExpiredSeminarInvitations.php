<?php

namespace App\Console\Commands\Training;

use App\Services\Training\SeminarInvitationService;
use Illuminate\Console\Command;

class CheckForExpiredSeminarInvitations extends Command
{
    protected $signature = 'training:check-for-expired-seminar-invitations';

    protected $description = 'Process seminar invitations that have expired without a response';

    public function handle(SeminarInvitationService $service): int
    {
        $service->expireUnrespondedInvitations();

        return Command::SUCCESS;
    }
}
