<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateMembers extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'Members:CertUpdate
                        {max_members=1000}
                        {--f|force= : If specified, only this CID will be checked.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update members using the CERT feeds.';

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
        $members = Account::where('id', '>=', 800000);

        if ($this->option('force')) {
            $members->where('id', $this->option('force'));
        } else {
            $members->where(function ($query) {
                $query->where('cert_checked_at', '<=', Carbon::now()->subDay())
                    ->orWhereNull('cert_checked_at');
            });
        }

        return $members->orderBy('cert_checked_at', 'ASC')
            ->limit($this->argument('max_members'))
            ->pluck('id');
    }
}
