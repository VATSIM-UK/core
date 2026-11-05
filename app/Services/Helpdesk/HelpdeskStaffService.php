<?php

declare(strict_types=1);

namespace App\Services\Helpdesk;

use App\Models\Mship\Account;
use App\Models\Mship\HelpdeskStaffAccount;
use App\Models\Mship\RoleHelpdeskStaffSetting;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
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

        $settings = RoleHelpdeskStaffSetting::query()
            ->with('extendedAccess')
            ->whereIn('role_id', $account->roles()->pluck('mship_role.id'))
            ->orderByDesc('priority')
            ->orderBy('role_id')
            ->get();

        $setting = $settings->first();

        if ($setting) {
            $this->provision($account, $setting, $this->extendedAccessFor($settings));

            return;
        }

        $this->deprovision($account);
    }

    /**
     * @param  Collection<int, array{dept_id: int, role_id: int}>|null  $extendedAccess
     */
    public function provision(Account $account, RoleHelpdeskStaffSetting $setting, ?Collection $extendedAccess = null): void
    {
        $connection = $this->connection();
        $extendedAccess ??= $setting->extendedAccess
            ->map(fn ($access): array => [
                'dept_id' => $access->helpdesk_department_id,
                'role_id' => $access->helpdesk_role_id,
            ]);

        $connection->transaction(function () use ($account, $connection, $extendedAccess, $setting): void {
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

            if ($extendedAccess->isNotEmpty()) {
                $connection->table($this->table('ost_staff_dept_access'))->insert(
                    $extendedAccess->map(fn (array $access): array => [
                        'staff_id' => $staffId,
                        'dept_id' => $access['dept_id'],
                        'role_id' => $access['role_id'],
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

    /**
     * The highest-priority role supplies the primary department and team.
     * Every other held role contributes its department as extended access.
     *
     * @param  Collection<int, RoleHelpdeskStaffSetting>  $settings
     * @return Collection<int, array{dept_id: int, role_id: int}>
     */
    private function extendedAccessFor(Collection $settings): Collection
    {
        $winner = $settings->first();
        $access = collect();

        foreach ($settings as $setting) {
            $entries = $setting->is($winner)
                ? $setting->extendedAccess->map(fn ($entry): array => [
                    'dept_id' => $entry->helpdesk_department_id,
                    'role_id' => $entry->helpdesk_role_id,
                ])
                : collect([[
                    'dept_id' => $setting->helpdesk_department_id,
                    'role_id' => $setting->helpdesk_role_id,
                ]])->merge($setting->extendedAccess->map(fn ($entry): array => [
                    'dept_id' => $entry->helpdesk_department_id,
                    'role_id' => $entry->helpdesk_role_id,
                ]));

            foreach ($entries as $entry) {
                if ($entry['dept_id'] === $winner->helpdesk_department_id || $access->contains('dept_id', $entry['dept_id'])) {
                    continue;
                }

                $access->push($entry);
            }
        }

        return $access;
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
