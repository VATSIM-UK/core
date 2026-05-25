<?php

declare(strict_types=1);

namespace App\Filament\Training\Support;

use Illuminate\Support\HtmlString;

final class MentoringReportLayout
{
    /** Vertical rhythm between criteria rows (no border separators). */
    public const CRITERION_ROW_CLASSES = 'mb-10 last:mb-0';

    public static function categorySectionTitle(string $categoryName): HtmlString
    {
        return new HtmlString(
            "<span class='text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white'>".e($categoryName).'</span>'
        );
    }
}
