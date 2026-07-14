<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Libraries\UKCP;
use App\Models\Atc\Position;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(fn () => $this->doSync($ukcp));
    }

    private function doSync(UKCP $ukcp): void
    {
        $ukcpPositions = $ukcp->getAllControllerPositions();

        if ($ukcpPositions->isEmpty()) {
            Log::warning('SyncUkcpPositions: UKCP API returned no positions. Skipping sync.');

            return;
        }

        $coreByCallsign = Position::all()->keyBy('callsign');
        $coreByUkcpId = Position::whereNotNull('ukcp_position_id')->get()->keyBy('ukcp_position_id');
        $ukcpIds = $ukcpPositions->pluck('id');

        $created = 0;
        $updated = 0;
        $deleted = 0;

        foreach ($ukcpPositions as $ukcpPosition) {
            $core = $coreByUkcpId->get($ukcpPosition->id) // Match by UKCP ID first (survives callsign changes)
                ?? $coreByCallsign->get($ukcpPosition->callsign); // Fall back to callsign (first-time linking)

            if ($core) {
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

                if (empty($changes)) {
                    continue;
                }

                if (! $this->dryRun) {
                    if (isset($changes['callsign']) && Position::where('callsign', $changes['callsign'])
                        ->where('id', '!=', $core->id)
                        ->exists()
                    ) {
                        Log::warning("SyncUkcpPositions: Skipping callsign update for {$core->callsign} -> {$changes['callsign']} because it would conflict with an existing position.");
                        unset($changes['callsign']);
                    }

                    if (! empty($changes)) {
                        $core->update($changes);
                        $updated++;
                    }
                } elseif (! empty($changes)) {
                    $updated++;
                }
            } else {
                if (! $this->dryRun) {
                    $name = $ukcpPosition->description ?: $ukcpPosition->callsign;

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

        $topDownUpdated = $this->syncTopDown($ukcp);

        // REMOVALS: soft-delete UKCP-synced positions that no longer exist in UKCP
        $positionsToRemove = Position::whereNotNull('ukcp_position_id')
            ->whereNotIn('ukcp_position_id', $ukcpIds);

        $removedCount = $positionsToRemove->count();

        if ($removedCount > 0 && ! $this->dryRun) {
            $idsToRemove = $positionsToRemove->pluck('id');

            Position::whereIn('id', $idsToRemove)->delete();
            Position::withTrashed()->whereIn('id', $idsToRemove)->update(['ukcp_position_id' => null]);
        }

        $deleted += $removedCount;

        $dryRunLabel = $this->dryRun ? ' (DRY RUN)' : '';

        Log::info("SyncUkcpPositions complete. Created: {$created}, Updated: {$updated}, Top-down updated: {$topDownUpdated}, Soft-deleted: {$deleted}{$dryRunLabel}");
    }

    private function syncTopDown(UKCP $ukcp): int
    {
        $v2Positions = $ukcp->getControllerPositionsV2Dependency();

        if ($v2Positions->isEmpty()) {
            return 0;
        }

        $v2ById = $v2Positions->keyBy('id');
        $coreByUkcpId = Position::whereNotNull('ukcp_position_id')->get()->keyBy('ukcp_position_id');
        $updated = 0;

        foreach ($coreByUkcpId as $ukcpId => $core) {
            $v2 = $v2ById->get($ukcpId);
            if (! $v2 || ! property_exists($v2, 'top_down')) {
                continue;
            }

            $topDown = $v2->top_down;
            $newValue = empty($topDown) ? [] : array_values($topDown);

            if ($core->top_down !== $newValue) {
                if (! $this->dryRun) {
                    $core->update(['top_down' => $newValue]);
                }
                $updated++;
            }
        }

        return $updated;
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping('sync-ukcp-positions'),
        ];
    }
}
