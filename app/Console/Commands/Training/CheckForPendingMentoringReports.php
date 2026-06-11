<?php

declare(strict_types=1);

namespace App\Console\Commands\Training;

use App\Models\Cts\Session;
use App\Notifications\Training\Mentoring\MentoringReportOutstandingNotification;
use Illuminate\Console\Command;

class CheckForPendingMentoringReports extends Command
{
    protected $signature = 'training:check-for-pending-mentoring-reports';

    protected $description = 'Send a notification to all mentors who have had an outstanding report for more than 72 hours';

    public function handle(): void
    {
        $pendingReports = Session::with('mentor')
            ->whereNull('filed')
            ->whereNotNull('mentor_id')
            ->whereNull('cancelled_datetime')
            ->where('noShow', 0)
            ->where('taken_date', '<', now()->subHours(72))
            ->get();

        foreach ($pendingReports as $pendingReport) {
            $mentorAccount = $pendingReport->mentor?->account;

            if (! $mentorAccount) {
                continue;
            }

            $mentorAccount->notify(new MentoringReportOutstandingNotification($pendingReport));
        }
    }
}
