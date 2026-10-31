<?php

declare(strict_types=1);

namespace App\Jobs\Helpdesk;

use App\Jobs\Job;
use App\Jobs\Mship\ReconcileHelpdeskStaff;
use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReconcileHelpdeskStaffRoleMembers extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public readonly int $roleId) {}

    public function handle(): void
    {
        Account::query()
            ->whereHas('roles', fn ($query) => $query->where('mship_role.id', $this->roleId))
            ->select('mship_account.id')
            ->orderBy('mship_account.id')
            ->chunkById(200, function ($accounts): void {
                foreach ($accounts as $account) {
                    ReconcileHelpdeskStaff::dispatch((int) $account->id);
                }
            }, 'mship_account.id', 'id');
    }
}
