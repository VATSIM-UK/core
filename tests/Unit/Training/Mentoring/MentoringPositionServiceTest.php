<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Libraries\UKCP;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentoringPositionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringPositionServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_returns_same_suffix_top_down_positions_that_exist_in_cts(): void
    {
        CtsPosition::factory()->create(['callsign' => 'TST_N_CTR']);
        CtsPosition::factory()->create(['callsign' => 'TST_S_CTR']);
        CtsPosition::factory()->create(['callsign' => 'EGLL_TWR']);

        $this->mock(UKCP::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTopDownPositionsForPosition')
                ->with('TST_CTR')
                ->andReturn(collect([
                    (object) ['callsign' => 'TST_S_CTR'],
                    (object) ['callsign' => 'TST_N_CTR'],
                    (object) ['callsign' => 'EGLL_TWR'],
                    (object) ['callsign' => 'TST_W_CTR'],
                ]));
        });

        $this->assertSame(
            ['TST_N_CTR', 'TST_S_CTR'],
            app(MentoringPositionService::class)->topDownCallsignsFor('TST_CTR')
        );
    }

    #[Test]
    public function it_returns_no_top_down_positions_when_ukcp_fails(): void
    {
        $this->mock(UKCP::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTopDownPositionsForPosition')
                ->with('TST_CTR')
                ->andReturnNull();
        });

        $this->assertSame([], app(MentoringPositionService::class)->topDownCallsignsFor('TST_CTR'));
    }

    #[Test]
    public function it_detects_whether_a_position_is_a_top_down_of_a_parent(): void
    {
        CtsPosition::factory()->create(['callsign' => 'TST_S_CTR']);

        $this->mock(UKCP::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTopDownPositionsForPosition')
                ->with('TST_CTR')
                ->andReturn(collect([
                    (object) ['callsign' => 'TST_S_CTR'],
                ]));
        });

        $service = app(MentoringPositionService::class);

        $this->assertTrue($service->isTopDownOf('TST_CTR', 'TST_S_CTR'));
        $this->assertFalse($service->isTopDownOf('TST_CTR', 'TST_N_CTR'));
    }

    #[Test]
    public function it_resolves_the_training_position_for_a_top_down_sector(): void
    {
        CtsPosition::factory()->create(['callsign' => 'TST_S_CTR']);

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['TST_CTR'],
        ]);

        $trainingPlace = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()->create([
            'training_position_id' => $trainingPosition->id,
        ]));

        $this->mock(UKCP::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTopDownPositionsForPosition')
                ->with('TST_CTR')
                ->andReturn(collect([
                    (object) ['callsign' => 'TST_S_CTR'],
                ]));
        });

        $service = app(MentoringPositionService::class);

        $this->assertSame('TST_CTR', $service->trainingPositionFor($trainingPlace, 'TST_CTR'));
        $this->assertSame('TST_CTR', $service->trainingPositionFor($trainingPlace, 'TST_S_CTR'));
        $this->assertNull($service->trainingPositionFor($trainingPlace, 'TST_N_CTR'));
    }
}
