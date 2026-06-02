<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor\Concerns;

trait RemembersTrainingGroupCategory
{
    private static string $categorySessionKey = 'training.mentoring.last_category';

    /**
     * Restore the training group category from the session when no value has
     * Call at the beginning of mount().
     */
    protected function rememberCategory(): void
    {
        if (empty($this->category) && $this->category !== '0') {
            $saved = session(static::$categorySessionKey);

            if ($saved !== null) {
                $this->category = $saved;
            }
        }
    }

    /**
     * Persist the current training group category to the session.
     * Call this after mount() has validated the category.
     */
    protected function saveCategoryToSession(): void
    {
        if (! empty($this->category)) {
            session([static::$categorySessionKey => $this->category]);
        }
    }
}
