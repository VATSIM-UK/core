<?php

declare(strict_types=1);

namespace App\Console\Commands\Training;

use App\Services\Training\OrphanedCtsSessionRequestCleanup;
use Illuminate\Console\Command;

class DeleteOrphanedCtsSessionRequests extends Command
{
    protected $signature = 'training-places:delete-orphaned-cts-session-requests';

    protected $description = 'Delete untaken CTS session requests for members who currently hold an active training place';

    public function handle(OrphanedCtsSessionRequestCleanup $cleanup): int
    {
        $deleted = $cleanup->deleteForActiveTrainingPlaces();

        $this->info(sprintf(
            'Deleted %d orphaned CTS session request(s).',
            $deleted,
        ));

        return self::SUCCESS;
    }
}
