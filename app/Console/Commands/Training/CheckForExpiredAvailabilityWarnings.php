<?php

declare(strict_types=1);

namespace App\Console\Commands\Training;

use App\Jobs\Training\ActionExpiredAvailabilityWarningRemoval;
use App\Services\Training\AvailabilityWarnings;
use Illuminate\Console\Command;

class CheckForExpiredAvailabilityWarnings extends Command
{
    protected $signature = 'training-places:check-for-expired-availability-warnings';

    protected $description = 'Check for expired availability warnings and remove training places where no subsequent successful check was made';

    public function handle(): int
    {
        $expiredWarnings = AvailabilityWarnings::getExpiredPendingWarnings(now());

        foreach ($expiredWarnings as $warning) {
            ActionExpiredAvailabilityWarningRemoval::dispatch($warning);
        }

        return Command::SUCCESS;
    }
}
