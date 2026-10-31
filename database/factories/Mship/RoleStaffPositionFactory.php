<?php

declare(strict_types=1);

namespace Database\Factories\Mship;

use App\Models\Mship\RoleStaffPosition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<RoleStaffPosition>
 */
class RoleStaffPositionFactory extends Factory
{
    protected $model = RoleStaffPosition::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => Role::query()->create([
                'name' => 'staff-position-'.fake()->unique()->uuid(),
                'guard_name' => 'web',
            ])->id,
            'department' => RoleStaffPosition::ATC_TRAINING_DEPARTMENT,
            'job_title' => fake()->jobTitle(),
            'is_department_lead' => false,
            'sort_order' => 0,
        ];
    }

    public function lead(): static
    {
        return $this->state([
            'is_department_lead' => true,
        ]);
    }
}
