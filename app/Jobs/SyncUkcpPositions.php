<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Libraries\UKCP;
use App\Models\Atc\Position;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class SyncUkcpPositions extends Job implements ShouldQueue
{
    public $tries = 3;

    public $backoff = 60;

    public $queue = 'default';

    private bool $dryRun;

    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
    }

    public function handle(UKCP $ukcp): void
    {
        $ukcpPositions = $ukcp->getAllControllerPositions();

        if ($ukcpPositions->isEmpty()) {
            Log::warning('SyncUkcpPositions: UKCP API returned no positions. Skipping sync.');

            return;
        }

        $corePositions = Position::all()->keyBy('callsign');
        $ukcpIds = $ukcpPositions->pluck('id');

        $created = 0;
        $updated = 0;
        $flagged = 0;

        foreach ($ukcpPositions as $ukcpPosition) {
            $core = $corePositions->get($ukcpPosition->callsign);

            if ($core) {
                // UPDATE - only frequency and ukcp_position_id, preserve everything else
                if ($core->frequency !== $ukcpPosition->frequency || $core->ukcp_position_id !== $ukcpPosition->id) {
                    if (! $this->dryRun) {
                        $core->update([
                            'frequency' => $ukcpPosition->frequency,
                            'ukcp_position_id' => $ukcpPosition->id,
                        ]);
                    }
                    $updated++;
                }
            } else {
                // CREATE - new position from UKCP
                $name = $ukcpPosition->description ?: $ukcpPosition->callsign;

                if (! $this->dryRun) {
                    Position::create([
                        'callsign' => $ukcpPosition->callsign,
                        'name' => $name,
                        'frequency' => $ukcpPosition->frequency,
                        'type' => Position::inferTypeFromCallsign($ukcpPosition->callsign),
                        'ukcp_position_id' => $ukcpPosition->id,
                        'temporarily_endorsable' => false,
                        'virtual' => false,
                    ]);
                }
                $created++;
            }
        }

        // REMOVALS — soft-delete UKCP-synced positions that no longer exist in UKCP
        $positionsToRemove = Position::whereNotNull('ukcp_position_id')
            ->whereNotIn('ukcp_position_id', $ukcpIds);

        $removedCount = $positionsToRemove->count();

        if (! $this->dryRun && $removedCount > 0) {
            $positionsToRemove->delete();
        }

        $flagged += $removedCount;

        Log::info("SyncUkcpPositions complete. Created: {$created}, Updated: {$updated}, Soft-deleted: {$flagged}".($this->dryRun ? ' (DRY RUN)' : ''));
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping('sync-ukcp-positions'),
        ];
    }
}
