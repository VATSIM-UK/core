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

        // Index Core positions by both callsign and ukcp_position_id for flexible matching
        $coreByCallsign = Position::all()->keyBy('callsign');
        $coreByUkcpId = Position::whereNotNull('ukcp_position_id')->get()->keyBy('ukcp_position_id');
        $ukcpIds = $ukcpPositions->pluck('id');

        $created = 0;
        $updated = 0;
        $flagged = 0;

        foreach ($ukcpPositions as $ukcpPosition) {
            $core = $coreByUkcpId->get($ukcpPosition->id) // Match by UKCP ID first (survives callsign changes)
                ?? $coreByCallsign->get($ukcpPosition->callsign); // Fall back to callsign (first-time linking)

            if ($core) {
                // Determine what needs updating
                $changes = [];

                if ($core->callsign !== $ukcpPosition->callsign) {
                    $changes['callsign'] = $ukcpPosition->callsign;
                }

                if ((float) $core->frequency !== (float) $ukcpPosition->frequency) {
                    $changes['frequency'] = $ukcpPosition->frequency;
                }

                if ($core->ukcp_position_id !== $ukcpPosition->id) {
                    $changes['ukcp_position_id'] = $ukcpPosition->id;
                }

                if (! empty($changes) && ! $this->dryRun) {
                    if (isset($changes['callsign']) && Position::where('callsign', $changes['callsign'])
                        ->where('id', '!=', $core->id)
                        ->exists()
                    ) {
                        Log::warning("SyncUkcpPositions: Skipping callsign update for {$core->callsign} -> {$changes['callsign']} because it would conflict with an existing position.");
                        unset($changes['callsign']);
                    }

                    if (! empty($changes)) {
                        $core->update($changes);
                    }
                }

                if (! empty($changes)) {
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

        // REMOVALS - soft-delete UKCP-synced positions that no longer exist in UKCP
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
