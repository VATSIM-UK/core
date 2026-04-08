<?php

use App\Models\Cts\ExaminerSettings;
use App\Models\Mship\Account;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private const ROLE_MAP = [
        'P1' => 'Pilot Examiner (P1)',
        'P2' => 'Pilot Examiner (P2)',
        'P3' => 'Pilot Examiner (P3)',
    ];

    public function up(): void
    {
        // Ensure all roles exist before assigning
        foreach (self::ROLE_MAP as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        ExaminerSettings::query()
            ->where(function ($query) {
                foreach (array_keys(self::ROLE_MAP) as $column) {
                    $query->orWhere($column, 1);
                }
            })
            ->each(function (ExaminerSettings $settings) {
                $account = Account::query()->find($settings->memberID);

                if (! $account) {
                    return;
                }

                foreach (self::ROLE_MAP as $column => $roleName) {
                    if ($settings->{$column} == 1) {
                        $account->assignRole($roleName);
                    }
                }
            });
    }

    public function down(): void
    {
        foreach (self::ROLE_MAP as $roleName) {
            $role = Role::findByName($roleName, 'web');
            $role?->users()->detach();
        }
    }
};
