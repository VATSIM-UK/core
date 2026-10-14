<?php

declare(strict_types=1);

namespace App\Console\Commands\Training;

use App\Services\Training\MentorInactivityService;
use Illuminate\Console\Command;

class RemoveInactiveMentoringPermissions extends Command
{
    protected $signature = 'training:remove-inactive-mentoring-permissions';

    protected $description = 'Remove mentoring permissions from mentors who have not mentored within a training group for six months';

    public function handle(MentorInactivityService $service): int
    {
        $removed = $service->removeInactiveMentoringPermissions();

        $this->info(sprintf(
            'Removed mentoring permissions for %d inactive mentor/training group combination(s).',
            $removed->count(),
        ));

        foreach ($removed as $assignment) {
            $this->line(sprintf(
                '- %s [%s] (last mentored: %s)',
                $assignment['account']->name,
                $assignment['category'],
                $assignment['last_mentored_at']?->toDateString() ?? 'never',
            ));
        }

        return self::SUCCESS;
    }
}
