<?php

namespace App\Console\Commands\VisitTransfer;

use App\Console\Commands\Command;
use App\Services\VisitTransfer\VisitingControllerInactivity;

class VisitingControllerInactivityCheck extends Command
{
    protected $signature = 'visit-transfer:check-inactivity';

    protected $description = 'Remove the visiting rights of controllers who no longer meet the GCAP activity requirements.';

    public function handle(VisitingControllerInactivity $service)
    {
        $summary = $service->process();

        $this->info(sprintf(
            'Checked %d visiting controller(s); %d eligible; %d removed.',
            $summary['checked'],
            $summary['eligible'],
            $summary['removed'],
        ));

        return self::SUCCESS;
    }
}
