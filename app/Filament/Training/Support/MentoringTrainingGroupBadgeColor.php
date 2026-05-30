<?php

declare(strict_types=1);

namespace App\Filament\Training\Support;

use App\Services\Training\MentorPermissionService;
use Filament\Support\Colors\Color;

final class MentoringTrainingGroupBadgeColor
{
    /**
     * Distinct Filament badge colors per mentoring training group (category).
     *
     * @var array<string, string|array<int, string>>
     */
    private const CATEGORY_COLORS = [
        'OBS to S1 Training' => Color::Sky,
        'S2 Training' => Color::Blue,
        'S3 Training' => Color::Emerald,
        'C1 Training' => Color::Amber,
        'Heathrow GMC' => Color::Rose,
        'Heathrow AIR' => Color::Violet,
        'Heathrow APC' => Color::Fuchsia,
        'P1 Training' => Color::Cyan,
        'P2 Training' => Color::Teal,
        'P3 Training' => Color::Indigo,
    ];

    public static function forCategory(?string $category): string|array
    {
        if ($category === null || $category === '') {
            return 'gray';
        }

        return self::CATEGORY_COLORS[$category] ?? 'gray';
    }

    public static function forCtsCallsign(string $callsign): string|array
    {
        $category = app(MentorPermissionService::class)->resolveCategoryForCtsCallsign($callsign);

        return self::forCategory($category);
    }
}
