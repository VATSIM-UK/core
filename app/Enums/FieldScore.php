<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FieldScore: int implements HasColor, HasLabel
{
    case NOT_SCORED = 0;
    case NOT_APPLICABLE = 1;
    case COVERED = 2;
    case DEVELOPING = 3;
    case GOOD = 4;
    case TEST_STANDARD = 5;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NOT_SCORED, self::NOT_APPLICABLE => 'N/A',
            self::COVERED => 'Covered',
            self::DEVELOPING => 'Developing',
            self::GOOD => 'Good',
            self::TEST_STANDARD => 'Test Standard',
        };
    }

    /**
     * Score options for mentoring conduct forms (CTS values 1–5).
     *
     * @return array<int, string>
     */
    public static function mentoringConductOptions(): array
    {
        return [
            self::NOT_APPLICABLE->value => 'Not Covered',
            self::COVERED->value => 'Covered',
            self::DEVELOPING->value => 'Developing',
            self::GOOD->value => 'Good',
            self::TEST_STANDARD->value => 'Test Standard',
        ];
    }

    public function toPercentage(): int
    {
        return match ($this) {
            self::NOT_SCORED, self::NOT_APPLICABLE => 0,
            self::COVERED => 25,
            self::DEVELOPING => 50,
            self::GOOD => 75,
            self::TEST_STANDARD => 100,
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NOT_SCORED, self::NOT_APPLICABLE => 'gray',
            self::COVERED => Color::Orange,
            self::DEVELOPING => Color::Amber,
            self::GOOD => Color::Lime,
            self::TEST_STANDARD => Color::Emerald,
        };
    }
}
