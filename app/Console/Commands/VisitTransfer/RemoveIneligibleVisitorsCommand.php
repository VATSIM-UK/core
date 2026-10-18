<?php

declare(strict_types=1);

namespace App\Console\Commands\VisitTransfer;

use App\Console\Commands\Command;
use App\Jobs\VisitTransfer\RemoveIneligibleVisitors;
use Illuminate\Console\ConfirmableTrait;

class RemoveIneligibleVisitorsCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'visit-transfer:remove-ineligible-visitors {--dry-run : Report what would change without writing anything} {--force : Skip the production confirmation prompt}';

    protected $description = 'Remove the visiting state from members who are no longer eligible to hold it.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $job = new RemoveIneligibleVisitors(dryRun: $dryRun);
        $job->handle();

        $summary = $job->summary;

        $this->info($dryRun
            ? sprintf(
                'Dry run complete. %d visitor(s) would be removed; %d would be returned to the controller roster.',
                $summary['removed'],
                $summary['returned_to_roster'],
            )
            : sprintf(
                'Removed %d visitor(s); returned %d to the controller roster.',
                $summary['removed'],
                $summary['returned_to_roster'],
            ));

        if ($summary['reasons'] !== []) {
            $this->table(
                ['Reason', 'Removed'],
                collect($summary['reasons'])
                    ->map(fn (int $count, string $reason): array => [RemoveIneligibleVisitors::reasonText($reason), $count])
                    ->values()
                    ->all(),
            );
        }

        return self::SUCCESS;
    }
}
