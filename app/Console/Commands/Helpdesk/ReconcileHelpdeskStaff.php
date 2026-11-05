<?php

declare(strict_types=1);

namespace App\Console\Commands\Helpdesk;

use App\Jobs\Mship\ReconcileHelpdeskStaff as ReconcileHelpdeskStaffJob;
use App\Models\Mship\Account;
use App\Models\Mship\HelpdeskStaffAccount;
use App\Models\Mship\RoleHelpdeskStaffSetting;
use Illuminate\Console\Command;

class ReconcileHelpdeskStaff extends Command
{
    protected $signature = 'helpdesk:sync-staff';

    protected $description = 'Reconcile role-managed osTicket staff accounts';

    public function handle(): int
    {
        if (blank(config('services.helpdesk.database'))) {
            $this->components->info('Helpdesk database is not configured; nothing to reconcile.');

            return self::SUCCESS;
        }

        $mappedRoleIds = RoleHelpdeskStaffSetting::query()->pluck('role_id');
        $accountIds = Account::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('mship_role.id', $mappedRoleIds))
            ->pluck('mship_account.id')
            ->merge(HelpdeskStaffAccount::query()->pluck('account_id'))
            ->unique();

        foreach ($accountIds as $accountId) {
            ReconcileHelpdeskStaffJob::dispatch((int) $accountId);
        }

        return self::SUCCESS;
    }
}
