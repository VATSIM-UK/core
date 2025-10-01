<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Entry;

class PracticalExamCriteriaResult extends Entry
{
    protected string $view = 'infolists.components.practical-exam-criteria-result';

    public function getColorForResult(): string
    {
        return match ($this->getState()) {
            'P' => '#B0EEBE',
            'M' => '#ffcc66',
            'R' => '#ff9966',
            'N' => '#999999',
            'F' => '#ce484b',
            default => '#999999',
        };
    }

    public function getTextColorForResult(): string
    {
        return match ($this->getState()) {
            'P' => '#1f2937',
            'M' => '#1f2937',
            'R' => '#1f2937',
            'N' => '#ffffff',
            'F' => '#ffffff',
            default => '#ffffff',
        };
    }
}
