<?php

namespace App\Infolists\Components;

use App\Enums\FieldScore;
use Filament\Infolists\Components\Entry;

class ProgressEntry extends Entry
{
    protected string $view = 'infolists.components.progress-entry';

    protected FieldScore $previousScore;

    protected FieldScore $bestScore;

    public function previous(FieldScore $score): static
    {
        $this->previousScore = $score;

        return $this;
    }

    public function best(FieldScore $score): static
    {
        $this->bestScore = $score;

        return $this;
    }

    public function getCurrentScore(): FieldScore
    {
        return $this->getState() ?? FieldScore::NOT_APPLICABLE;
    }

    public function getPreviousScore(): FieldScore
    {
        return $this->previousScore ?? FieldScore::NOT_APPLICABLE;
    }

    public function getBestScore(): FieldScore
    {
        return $this->bestScore ?? FieldScore::NOT_APPLICABLE;
    }

    public function getDelta(): int
    {
        return $this->getCurrentScore()->value - $this->getPreviousScore()->value;
    }
}
