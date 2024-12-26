<?php

namespace Database\Seeders\Testing;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use Illuminate\Database\Seeder;

class PositionsAndEndorsementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Positions
        $milPositions = collect(['EGOV', 'EGQL', 'EGQL', 'EGVN', 'EGVN', 'EGYE', 'EGXC', 'EGYD', 'EGYD', 'EGDM', 'EGDY', 'EGNO', 'EGOS', 'EGQS', 'EGUB', 'EGUL', 'EGUN', 'EGUN', 'EGVA', 'EGVO', 'EGYM', 'EGWU', 'EGWU', 'EGXC', 'EGWC', 'EGXE', 'EGDI', 'EGVP', 'EGOQ', 'EGDO', 'EGOE', 'EGXW', 'EGOW', 'EGUW', 'EGXT', 'LXGB', 'FHSH', 'EGYP', 'LCRA', 'EGKT', 'EGVL', 'EGWN', 'EGXH', 'EGKN', 'EGXY', 'EGDJ', 'EGDN', 'EGUB', 'EGXY', 'EGXZ', 'EGDY', 'EGXT', 'EGYP', 'LCRA'])->map(function ($position) {
            return Position::factory()->create(['callsign' => $position.'_TWR', 'name' => $position.'_TWR', 'type' => Position::TYPE_TOWER])->id;
        });

        $heathrowPositions = collect(['DEL' => Position::TYPE_DELIVERY, '1_GND' => Position::TYPE_GROUND, '2_GND' => Position::TYPE_GROUND, '3_GND' => Position::TYPE_GROUND, 'N_TWR' => Position::TYPE_TOWER, 'S_TWR' => Position::TYPE_TOWER, 'N_APP' => Position::TYPE_APPROACH, 'F_APP' => Position::TYPE_APPROACH, 'F_APP' => Position::TYPE_APPROACH])->map(function ($val, $key) {
            return Position::factory()->create(['callsign' => 'EGLL_'.$key, 'name' => 'EGLL_'.$key, 'type' => $val])->id;
        });

        $gatwickPositions = collect(['DEL' => Position::TYPE_DELIVERY, 'GND' => Position::TYPE_GROUND, 'TWR' => Position::TYPE_TOWER, 'APP' => Position::TYPE_APPROACH])->map(function ($val, $key) {
            return Position::factory()->create(['callsign' => 'EGKK_'.$key, 'name' => 'EGKK_'.$key, 'type' => $val])->id;
        });

        // Position Groups
        $heathrowPG = PositionGroup::factory()->create(['name' => 'Heathrow (APP)']);
        $heathrowPG->positions()->sync($heathrowPositions);
        $militaryPG = PositionGroup::factory()->create(['name' => 'Military (TWR)']);
        $militaryPG->positions()->sync($milPositions);
    }
}
