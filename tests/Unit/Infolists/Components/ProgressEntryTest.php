<?php

namespace Tests\Unit\Infolists\Components;

use App\Enums\FieldScore;
use App\Infolists\Components\ProgressEntry;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProgressEntryTest extends TestCase
{
    #[Test]
    public function it_returns_not_applicable_as_default_current_score(): void
    {
        $entry = ProgressEntry::make('progress')->state(null);

        $this->assertSame(FieldScore::NOT_APPLICABLE, $entry->getCurrentScore());
    }

    #[Test]
    public function it_returns_not_applicable_when_state_is_not_applicable(): void
    {
        $entry = ProgressEntry::make('progress')->state(FieldScore::NOT_APPLICABLE);

        $this->assertSame(FieldScore::NOT_APPLICABLE, $entry->getCurrentScore());
    }

    #[Test]
    public function it_returns_the_current_score_from_state(): void
    {
        $entry = ProgressEntry::make('progress')->state(FieldScore::GOOD);

        $this->assertSame(FieldScore::GOOD, $entry->getCurrentScore());
    }

    #[Test]
    public function it_returns_not_applicable_as_default_previous_score(): void
    {
        $entry = ProgressEntry::make('progress');

        $this->assertSame(FieldScore::NOT_APPLICABLE, $entry->getPreviousScore());
    }

    #[Test]
    public function it_returns_the_explicitly_set_previous_score(): void
    {
        $entry = ProgressEntry::make('progress')->previous(FieldScore::COVERED);

        $this->assertSame(FieldScore::COVERED, $entry->getPreviousScore());
    }

    #[Test]
    public function it_returns_not_applicable_as_default_best_score(): void
    {
        $entry = ProgressEntry::make('progress');

        $this->assertSame(FieldScore::NOT_APPLICABLE, $entry->getBestScore());
    }

    #[Test]
    public function it_returns_the_explicitly_set_best_score(): void
    {
        $entry = ProgressEntry::make('progress')->best(FieldScore::TEST_STANDARD);

        $this->assertSame(FieldScore::TEST_STANDARD, $entry->getBestScore());
    }

    #[Test]
    public function it_calculates_positive_delta_when_score_improved(): void
    {
        $entry = ProgressEntry::make('progress')
            ->state(FieldScore::GOOD)
            ->previous(FieldScore::COVERED);

        $this->assertSame(2, $entry->getDelta());
    }

    #[Test]
    public function it_calculates_negative_delta_when_score_declined(): void
    {
        $entry = ProgressEntry::make('progress')
            ->state(FieldScore::COVERED)
            ->previous(FieldScore::DEVELOPING);

        $this->assertSame(-1, $entry->getDelta());
    }

    #[Test]
    public function it_calculates_zero_delta_when_score_unchanged(): void
    {
        $entry = ProgressEntry::make('progress')
            ->state(FieldScore::DEVELOPING)
            ->previous(FieldScore::DEVELOPING);

        $this->assertSame(0, $entry->getDelta());
    }
}
