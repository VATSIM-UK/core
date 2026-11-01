<?php

declare(strict_types=1);

namespace App\Models\Mship;

use App\Models\Model;
use App\Observers\RoleHelpdeskStaffSettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

#[ObservedBy([RoleHelpdeskStaffSettingObserver::class])]
class RoleHelpdeskStaffSetting extends Model
{
    protected $table = 'mship_role_helpdesk_staff_setting';

    protected $fillable = [
        'role_id',
        'helpdesk_department_id',
        'helpdesk_team_id',
        'helpdesk_role_id',
        'priority',
    ];

    protected $casts = [
        'helpdesk_department_id' => 'integer',
        'helpdesk_team_id' => 'integer',
        'helpdesk_role_id' => 'integer',
        'priority' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function extendedAccess(): HasMany
    {
        return $this->hasMany(RoleHelpdeskStaffAccess::class, 'setting_id');
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function syncForRole(Role $role, array $state): void
    {
        if (! ($state['helpdesk_enabled'] ?? false)) {
            static::query()->where('role_id', $role->getKey())->delete();

            return;
        }

        $required = [
            'helpdesk_department_id',
            'helpdesk_team_id',
            'helpdesk_role_id',
        ];

        foreach ($required as $field) {
            if (blank($state[$field] ?? null)) {
                throw ValidationException::withMessages([
                    "data.{$field}" => 'This field is required when osTicket agent sync is enabled.',
                ]);
            }
        }

        $primaryDepartmentId = (int) $state['helpdesk_department_id'];
        $extendedDepartmentIds = collect($state['helpdesk_extended_access'] ?? [])
            ->pluck('department_id')
            ->filter()
            ->map(fn ($id): int => (int) $id);

        if ($extendedDepartmentIds->contains($primaryDepartmentId)) {
            throw ValidationException::withMessages([
                'data.helpdesk_extended_access' => 'The primary department cannot also be configured as extended access.',
            ]);
        }

        DB::transaction(function () use ($role, $state): void {
            $setting = static::query()->updateOrCreate(
                ['role_id' => $role->getKey()],
                [
                    'helpdesk_department_id' => (int) $state['helpdesk_department_id'],
                    'helpdesk_team_id' => (int) $state['helpdesk_team_id'],
                    'helpdesk_role_id' => (int) $state['helpdesk_role_id'],
                    'priority' => (int) ($state['helpdesk_priority'] ?? 0),
                ],
            );

            $setting->extendedAccess()->delete();

            $access = collect($state['helpdesk_extended_access'] ?? [])
                ->filter(fn (array $item): bool => filled($item['department_id'] ?? null) && filled($item['role_id'] ?? null))
                ->unique('department_id')
                ->map(fn (array $item): array => [
                    'helpdesk_department_id' => (int) $item['department_id'],
                    'helpdesk_role_id' => (int) $item['role_id'],
                ]);

            $setting->extendedAccess()->createMany($access->all());
        });
    }
}
