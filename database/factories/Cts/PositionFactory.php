<?php

namespace Database\Factories\Cts;

use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rts_id' => 1,
            'callsign' => $this->faker->randomElement(['EGKK_APP', 'EGCC_APP', 'LON_SC_CTR', 'EGGP_GND']),
            'rating' => 1,
            'auto_rating' => 12,
            'vis_roster' => 1,
            'anon_requests' => 0,
            'prog_sheet_id' => 1,
            'prog_sheet_assign_by' => 1,
        ];
    }
}