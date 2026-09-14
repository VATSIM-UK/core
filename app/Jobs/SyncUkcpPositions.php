<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Libraries\UKCP;
use App\Models\Atc\Position;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncUkcpPositions extends Job
{
    private bool $dryRun;

    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
    }

    public function handle(UKCP $ukcp): void
    {
        Position::$bypassUkcpProtection = true;

        try {
            DB::transaction(fn () => $this->doSync($ukcp));
        } finally {
            Position::$bypassUkcpProtection = false;
        }
    }

    private function doSync(UKCP $ukcp): void
    {
        $ukcpPositions = $ukcp->getAllControllerPositions();

        if ($ukcpPositions->isEmpty()) {
            Log::warning('SyncUkcpPositions: UKCP API returned no positions. Skipping sync.');

            return;
        }

        $coreByCallsign = Position::withTrashed()->orderByRaw('deleted_at IS NULL DESC')->get()->keyBy('callsign');
        $coreByUkcpId = Position::withTrashed()->whereNotNull('ukcp_position_id')->get()->keyBy('ukcp_position_id');
        $ukcpIds = $ukcpPositions->pluck('id');

        $created = 0;
        $updated = 0;
        $restored = 0;
        $deleted = 0;

        foreach ($ukcpPositions as $ukcpPosition) {
            $core = $coreByUkcpId->get($ukcpPosition->id)
                ?? $coreByCallsign->get($ukcpPosition->callsign);

            if ($core) {
                $wasTrashed = $core->trashed();

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

                if (empty($changes) && ! $wasTrashed) {
                    continue;
                }

                if (! $this->dryRun) {
                    if ($wasTrashed) {
                        $core->restore();
                        $restored++;
                    }

                    if (isset($changes['callsign']) && Position::where('callsign', $changes['callsign'])
                        ->where('id', '!=', $core->id)
                        ->exists()
                    ) {
                        Log::warning('SyncUkcpPositions: Skipping callsign update because it would conflict with an existing position', [
                            'callsign' => $core->callsign,
                            'new_callsign' => $changes['callsign'],
                        ]);
                        unset($changes['callsign']);
                    }

                    if (! empty($changes)) {
                        $core->update($changes);
                        $updated++;
                    }
                } elseif ($wasTrashed) {
                    $restored++;
                    if (! empty($changes)) {
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

        $positionsToRemove = Position::whereNotNull('ukcp_position_id')
            ->whereNotIn('ukcp_position_id', $ukcpIds);

        $removedCount = $positionsToRemove->count();

        if ($removedCount > 0 && ! $this->dryRun) {
            $idsToRemove = $positionsToRemove->pluck('id');

            Position::whereIn('id', $idsToRemove)->delete();
            Position::withTrashed()->whereIn('id', $idsToRemove)->update(['ukcp_position_id' => null]);
        }

        $deleted += $removedCount;

        Log::info('SyncUkcpPositions complete', [
            'created' => $created,
            'updated' => $updated,
            'restored' => $restored,
            'top_down_updated' => $topDownUpdated,
            'soft_deleted' => $deleted,
            'dry_run' => $this->dryRun,
        ]);
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
}
