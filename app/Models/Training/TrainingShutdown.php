<?php

namespace App\Models\Training;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingShutdown extends Model
{
    use SoftDeletes;

    protected $table = 'training_shutdowns';

    protected $fillable = [
        'department',
        'begins_at',
        'ends_at',
        'reason',
    ];

    protected $casts = [
        'begins_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeForDepartment(Builder $query, string $department): Builder
    {
        return $query->where('department', $department);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('begins_at', '<=', now())->where('ends_at', '>=', now());
    }

    public static function currentForDepartment(string $department): ?self
    {
        return static::query()->forDepartment($department)->current()->first();
    }

    /**
     * Whether a warning should not be actioned because a shutdown covers its expiry,
     * or because a shutdown is currently active.
     */
    public static function suppressesRemoval(string $department, Carbon $expiresAt): bool
    {
        $coversExpiry = static::query()
            ->forDepartment($department)
            ->where('begins_at', '<=', $expiresAt)
            ->where('ends_at', '>=', $expiresAt)
            ->exists();

        return $coversExpiry || static::currentForDepartment($department) !== null;
    }

    public function isActive(): bool
    {
        return now()->between($this->begins_at, $this->ends_at);
    }
}
