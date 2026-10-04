<?php

declare(strict_types=1);

namespace Tests\Unit\Training;

use App\Enums\FieldScore;
use App\Filament\Training\Support\MentoringReportScores;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringReportScoresTest extends TestCase
{
    #[Test]
    public function best_score_ignores_sessions_not_in_the_eligible_list(): void
    {
        // Session 20 is a later, higher-scoring session not eligible for session 10's report.
        $scoreMap = [
            1 => [
                10 => FieldScore::DEVELOPING,
                20 => FieldScore::TEST_STANDARD,
            ],
        ];

        $bestScore = MentoringReportScores::bestScore($scoreMap, 1, [10]);

        $this->assertSame(FieldScore::DEVELOPING, $bestScore);
    }

    #[Test]
    public function best_score_includes_the_highest_score_among_eligible_sessions(): void
    {
        $scoreMap = [
            1 => [
                10 => FieldScore::COVERED,
                20 => FieldScore::GOOD,
                30 => FieldScore::TEST_STANDARD,
            ],
        ];

        $bestScore = MentoringReportScores::bestScore($scoreMap, 1, [10, 20]);

        $this->assertSame(FieldScore::GOOD, $bestScore);
    }

    #[Test]
    public function best_score_session_id_ignores_sessions_not_in_the_eligible_list(): void
    {
        $scoreMap = [
            1 => [
                10 => FieldScore::DEVELOPING,
                20 => FieldScore::TEST_STANDARD,
            ],
        ];

        $bestScoreSessionId = MentoringReportScores::bestScoreSessionId($scoreMap, 1, [10]);

        $this->assertSame(10, $bestScoreSessionId);
    }
}
