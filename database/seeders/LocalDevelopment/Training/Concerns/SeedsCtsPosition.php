<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training\Concerns;

use App\Models\Atc\Position;
use App\Models\Cts\Position as CtsPosition;

/**
 * Ensures a callsign exists on both the core and CTS databases.
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
trait SeedsCtsPosition
{
    /**
     * @return array{core: Position, cts: CtsPosition}
     */
    protected function seedCtsPosition(string $callsign, ?int $type = null): array
    {
        $corePosition = Position::query()->firstOrCreate(
            ['callsign' => $callsign],
            [
                'name' => $callsign,
                'type' => $type ?? Position::TYPE_TOWER,
            ],
        );

        $ctsPosition = CtsPosition::query()->firstOrCreate(
            ['callsign' => $callsign],
            [
                'rts_id' => 1,
                'rating' => 1,
                'auto_rating' => 12,
                'vis_roster' => 1,
                'anon_requests' => 0,
                'prog_sheet_id' => 1,
                'prog_sheet_assign_by' => 1,
            ],
        );

        return [
            'core' => $corePosition,
            'cts' => $ctsPosition,
        ];
    }
}
