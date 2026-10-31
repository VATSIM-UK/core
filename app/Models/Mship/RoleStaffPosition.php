<?php

declare(strict_types=1);

namespace App\Models\Mship;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RoleStaffPosition extends Model
{
    use HasFactory;

    public const ATC_TRAINING_DEPARTMENT = 'ATC Training';

    public const TECHNOLOGY_DEPARTMENT = 'Technology';

    protected $table = 'mship_role_staff_position';

    protected $fillable = [
        'role_id',
        'department',
        'job_title',
        'is_department_lead',
        'sort_order',
    ];

    protected $casts = [
        'is_department_lead' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function syncForRole(Role $role, array $state): void
    {
        $department = trim((string) ($state['staff_department'] ?? ''));
        $jobTitle = trim((string) ($state['staff_job_title'] ?? ''));

        if ($department === '' && $jobTitle === '') {
            static::query()->where('role_id', $role->getKey())->delete();

            return;
        }

        if ($department === '' || $jobTitle === '') {
            throw ValidationException::withMessages([
                'data.staff_department' => 'Department and job title are both required to list this role on the staff page.',
                'data.staff_job_title' => 'Department and job title are both required to list this role on the staff page.',
            ]);
        }

        static::query()->updateOrCreate(
            ['role_id' => $role->getKey()],
            [
                'department' => $department,
                'job_title' => $jobTitle,
                'is_department_lead' => (bool) ($state['staff_is_department_lead'] ?? false),
                'sort_order' => (int) ($state['staff_sort_order'] ?? 0),
            ],
        );
    }
}
