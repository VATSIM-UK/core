<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncUkcpPositions as SyncUkcpPositionsJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncUkcpPositions extends Command
{
    protected $signature = 'ukcp:sync-positions
        {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Sync positions from UKCP API to the core positions table.';

    public function handle(): int
    {
        $lockKey = 'ukcp:sync-positions:lock';

        if (! Cache::add($lockKey, true, 600)) {
            $this->warn('Another sync is already in progress. Skipping.');

            return 0;
        }

        try {
            $dryRun = (bool) $this->option('dry-run');

            if ($dryRun) {
                $this->warn('DRY RUN - no changes will be written to the database.');
            }

            $this->info('Fetching positions from UKCP API...');

            $job = new SyncUkcpPositionsJob($dryRun);
            $job->handle(app(\App\Libraries\UKCP::class));

            $this->info('SyncUkcpPositions job completed.');

            if ($dryRun) {
                $this->warn('DRY RUN - no changes were written. Run without --dry-run to apply.');
            }

            Log::info('ukcp:sync-positions command completed.'.($dryRun ? ' (DRY RUN)' : ''));

            return 0;
        } finally {
            Cache::forget($lockKey);
        }
    }
}
