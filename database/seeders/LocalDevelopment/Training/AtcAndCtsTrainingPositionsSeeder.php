<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

use App\Models\Atc\Position;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Database\Seeders\LocalDevelopment\Training\Concerns\SeedsCtsPosition;
use Database\Seeders\Testing\PositionsAndEndorsementsSeeder;
use Illuminate\Database\Seeder;

/**
 * Seeds ATC position groups, aligned CTS callsigns, and training position records.
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
class AtcAndCtsTrainingPositionsSeeder extends Seeder
{
    use SeedsCtsPosition;

    /**
     * @var array<string, array{cts_positions: list<string>, cts_primary_position: string, exam_callsign: string, category: string, type?: int}>
     */
    private const TRAINING_POSITIONS = [
        'EGKK_TWR' => [
            'cts_positions' => ['EGKK_TWR'],
            'cts_primary_position' => 'EGKK_TWR',
            'exam_callsign' => 'EGKK_TWR',
            'category' => 'S2 Training',
            'type' => Position::TYPE_TOWER,
        ],
        'EGLL_N_APP' => [
            'cts_positions' => ['EGLL_N_APP'],
            'cts_primary_position' => 'EGLL_N_APP',
            'exam_callsign' => 'EGLL_N_APP',
            'category' => 'S3 Training',
            'type' => Position::TYPE_APPROACH,
        ],
    ];

    public function run(): void
    {
        $this->call(PositionsAndEndorsementsSeeder::class);

        foreach (self::TRAINING_POSITIONS as $callsign => $config) {
            $positions = $this->seedCtsPosition($callsign, $config['type'] ?? null);

            $trainingPosition = TrainingPosition::query()->updateOrCreate(
                ['position_id' => $positions['core']->id],
                [
                    'cts_positions' => $config['cts_positions'],
                    'cts_primary_position' => $config['cts_primary_position'],
                    'exam_callsign' => $config['exam_callsign'],
                    'category' => $config['category'],
                ],
            );

            DevTrainingFoundation::$trainingPositionsByCallsign[$callsign] = $trainingPosition;
        }

        $this->command?->info('ATC position groups, CTS callsigns, and training positions seeded.');
    }
}
