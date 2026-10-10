<?php

namespace App\Console\Commands\Training;

use App\Services\Training\SeminarTheoryExamReminderService;
use Illuminate\Console\Command;

class CheckSeminarTheoryExamReminders extends Command
{
    protected $signature = 'training:check-seminar-theory-exam-reminders';

    protected $description = 'Check due theory exam reminders';

    public function handle(SeminarTheoryExamReminderService $service): int
    {
        $service->checkDueReminders();

        return Command::SUCCESS;
    }
}
