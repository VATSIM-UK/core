<?php

namespace App\Console\Commands\Roster;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Repositories\Cts\ExamResultRepository;
use Illuminate\Console\Command;

class CheckForNewS1ExamPasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roster:check-new-s1-exams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a query to CTS for new exam passes and add them to the roster if applicable.';

    /**
     * Execute the console command.
     */
    public function handle(ExamResultRepository $repository)
    {
        $recentSuccessfulS1Exams = $repository->getRecentPassedExamsOfType('OBS');

        foreach ($recentSuccessfulS1Exams as $exam) {
            $ctsMember = Member::where('id', $exam->student_id)->first();
            $coreAccount = Account::find($ctsMember->cid);

            if (! $coreAccount) {
                $this->error("Could not find account for student ID {$exam->student_id}.");

                continue;
            }

            if (! $coreAccount->hasState(State::findByCode('DIVISION'))) {
                $this->error("Account {$coreAccount->id} does not have the DIVISION state.");

                continue;
            }

            $isAlreadyOnRoster = Roster::where('account_id', $coreAccount->id)->exists();
            if (! $isAlreadyOnRoster) {
                Roster::create([
                    'account_id' => $coreAccount->id,
                ]);
                $this->info("Added account {$coreAccount->id} to the roster.");

                return;
            }
        }
    }
}
