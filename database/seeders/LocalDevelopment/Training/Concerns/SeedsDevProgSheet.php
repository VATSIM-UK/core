<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training\Concerns;

use App\Models\Cts\ProgSheet;
use App\Models\Cts\ProgSheetCategory;
use App\Models\Cts\ProgSheetField;

/**
 * Idempotent progress sheet fixtures for local mentoring conduct testing.
 */
trait SeedsDevProgSheet
{
    protected function seedDevProgSheet(int $progSheetId = 1): void
    {
        ProgSheet::query()->updateOrCreate(
            ['prog_sheet_id' => $progSheetId],
            [
                'name' => 'Dev TWR Progress Sheet',
                'created_by' => 0,
                'created_date' => now(),
                'disabled' => 0,
            ],
        );

        $category = ProgSheetCategory::query()->updateOrCreate(
            [
                'prog_sheet_id' => $progSheetId,
                'catName' => 'General Control',
            ],
            [
                'disabled' => 0,
            ],
        );

        ProgSheetField::query()->updateOrCreate(
            [
                'prog_sheet_id' => $progSheetId,
                'field' => 'Radio telephony',
            ],
            [
                'catId' => $category->catId,
                'groupId' => 1,
                'created_by' => 0,
                'created_date' => now(),
                'disabled' => 0,
            ],
        );

        ProgSheetField::query()->updateOrCreate(
            [
                'prog_sheet_id' => $progSheetId,
                'field' => 'Separation standards',
            ],
            [
                'catId' => $category->catId,
                'groupId' => 2,
                'created_by' => 0,
                'created_date' => now(),
                'disabled' => 0,
            ],
        );
    }
}
