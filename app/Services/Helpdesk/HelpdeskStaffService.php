<?php

declare(strict_types=1);

namespace App\Services\Helpdesk;

use App\Models\Mship\Account;
use App\Models\Mship\HelpdeskStaffAccount;
use App\Models\Mship\RoleHelpdeskStaffSetting;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class HelpdeskStaffService
{
    public function reconcile(int|Account $account): void
    {
        $account = $account instanceof Account
            ? $account->fresh()
            : Account::query()->find($account);

        if (! $account) {
            return;
        }

        $setting = RoleHelpdeskStaffSetting::query()
            ->with('extendedAccess')
            ->whereIn('role_id', $account->roles()->pluck('mship_role.id'))
            ->orderByDesc('priority')
            ->orderBy('role_id')
            ->first();

        if ($setting) {
            $this->provision($account, $setting);

            return;
        }

        $this->deprovision($account);
    }

    public function provision(Account $account, RoleHelpdeskStaffSetting $setting): void
    {
        $connection = $this->connection();

        $connection->transaction(function () use ($account, $connection, $setting): void {
            $staff = $connection->table($this->table('ost_staff'))
                ->where('username', $this->username($account))
                ->lockForUpdate()
                ->first();

            $attributes = [
                'dept_id' => $setting->helpdesk_department_id,
                'role_id' => $setting->helpdesk_role_id,
                'username' => $this->username($account),
                'firstname' => Str::substr($account->name_first, 0, 32),
                'lastname' => Str::substr($account->name_last, 0, 32),
                'email' => $this->email($account),
                'isactive' => 1,
                'updated' => now(),
            ];

            if ($staff) {
                $connection->table($this->table('ost_staff'))
                    ->where('staff_id', $staff->staff_id)
                    ->update($attributes);

                $staffId = (int) $staff->staff_id;
            } else {
                $staffId = (int) $connection->table($this->table('ost_staff'))->insertGetId($attributes + [
                    'passwd' => null,
                    'backend' => null,
                    'signature' => '',
                    'created' => now(),
                ], 'staff_id');
            }

            $connection->table($this->table('ost_team_member'))
                ->where('staff_id', $staffId)
                ->delete();
            $connection->table($this->table('ost_team_member'))->insert([
                'team_id' => $setting->helpdesk_team_id,
                'staff_id' => $staffId,
                'flags' => 0,
            ]);

            $connection->table($this->table('ost_staff_dept_access'))
                ->where('staff_id', $staffId)
                ->delete();

            if ($setting->extendedAccess->isNotEmpty()) {
                $connection->table($this->table('ost_staff_dept_access'))->insert(
                    $setting->extendedAccess->map(fn ($access): array => [
                        'staff_id' => $staffId,
                        'dept_id' => $access->helpdesk_department_id,
                        'role_id' => $access->helpdesk_role_id,
                        'flags' => 1,
                    ])->all(),
                );
            }

            HelpdeskStaffAccount::query()
                ->where('helpdesk_staff_id', $staffId)
                ->where('account_id', '!=', $account->id)
                ->delete();

            HelpdeskStaffAccount::query()->updateOrCreate(
                ['account_id' => $account->id],
                ['helpdesk_staff_id' => $staffId],
            );
        });
    }

    public function deprovision(Account $account): void
    {
        $connection = $this->connection();

        $connection->transaction(function () use ($account, $connection): void {
            $managed = HelpdeskStaffAccount::query()
                ->where('account_id', $account->id)
                ->lockForUpdate()
                ->first();

            $staff = $connection->table($this->table('ost_staff'))
                ->where(function ($query) use ($account, $managed): void {
                    if ($managed) {
                        $query->where('staff_id', $managed->helpdesk_staff_id)
                            ->orWhere('username', $this->username($account));

                        return;
                    }

                    $query->where('username', $this->username($account));
                })
                ->lockForUpdate()
                ->first();

            if (! $staff) {
                $managed?->delete();

                return;
            }

            $staffId = (int) $staff->staff_id;
            $poster = trim("{$staff->firstname} {$staff->lastname}");

            $connection->table($this->table('ost_staff'))
                ->where('staff_id', $staffId)
                ->delete();
            $connection->table($this->table('ost_ticket'))
                ->where('staff_id', $staffId)
                ->update(['staff_id' => 0]);
            $connection->table($this->table('ost_thread_entry'))
                ->where('staff_id', $staffId)
                ->update([
                    'staff_id' => 0,
                    'poster' => $poster,
                ]);
            $connection->table($this->table('ost_team_member'))
                ->where('staff_id', $staffId)
                ->delete();
            $connection->table($this->table('ost_staff_dept_access'))
                ->where('staff_id', $staffId)
                ->delete();

            HelpdeskStaffAccount::query()
                ->where('account_id', $account->id)
                ->delete();
        });
    }

    public function username(Account $account): string
    {
        return "s{$account->id}";
    }

    public function email(Account $account): string
    {
        return Str::slug($account->name_first, '.')
            .'.'
            .Str::slug($account->name_last, '.')
            .'@vatsim.uk';
    }

    private function table(string $table): string
    {
        $database = config('services.helpdesk.database');

        if (blank($database)) {
            throw new RuntimeException('HELPDESK_DATABASE is not configured.');
        }

        return "{$database}.{$table}";
    }

    private function connection(): Connection
    {
        return DB::connection(config('services.helpdesk.connection', 'mysql'));
    }
}
