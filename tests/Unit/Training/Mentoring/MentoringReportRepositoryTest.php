<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Repositories\Cts\MentoringReportRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringReportRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private MentoringReportRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(MentoringReportRepository::class);
    }

    #[Test]
    public function get_primary_position_returns_cts_primary_position_not_first_related_callsign(): void
    {
        TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_S_APP', 'EGLL_N_APP'],
            'cts_primary_position' => 'EGLL_N_APP',
        ]);

        $this->assertSame('EGLL_N_APP', $this->repository->getPrimaryPosition('EGLL_S_APP'));
        $this->assertSame('EGLL_N_APP', $this->repository->getPrimaryPosition('EGLL_N_APP'));
    }

    #[Test]
    public function get_primary_position_falls_back_to_session_position_when_training_position_not_found(): void
    {
        $this->assertSame('EGKK_TWR', $this->repository->getPrimaryPosition('EGKK_TWR'));
    }

    #[Test]
    public function get_primary_position_falls_back_to_session_position_when_cts_primary_position_is_empty(): void
    {
        TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_APP'],
            'cts_primary_position' => null,
        ]);

        $this->assertSame('EGLL_APP', $this->repository->getPrimaryPosition('EGLL_APP'));
    }
}
