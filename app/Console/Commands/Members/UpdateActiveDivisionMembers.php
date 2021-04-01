<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateActiveDivisionMembers extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'DivMembers:CertUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update active division members using the CERT feeds.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $members = $this->getMembers();

        foreach ($members as $member) {
            UpdateMember::dispatch($member);
            $this->log("$member added to update queue");
        }
    }

    protected function getMembers()
    {
        $members = Account::where('mship_account.id', '>=', 800000);

        $members
            ->leftJoin('mship_account_state', 'mship_account_state.account_id', '=', 'mship_account.id')
            ->where(function ($query) {
                $query->where('mship_account.cert_checked_at', '<=', Carbon::now()->subDay())
                    ->orWhereNull('mship_account.cert_checked_at');
            })
            ->where('mship_account.inactive', 0)
            ->where('mship_account_state.state_id', '=', State::findByCode('DIVISION')->id)
            ->whereNull('mship_account_state.end_at');

        return $members->orderBy('mship_account.cert_checked_at', 'ASC')
            ->pluck('mship_account.id');
    }
}
