<?php

namespace App\Models\Mship;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Achievement extends Model
{
    use SoftDeletes;

    protected $table = 'mship_achievement';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'description',
        'image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function booted(): void
    {
        static::updating(function (Achievement $achievement) {
            if ($achievement->isDirty('image')) {
                $oldImage = $achievement->getOriginal('image');

                if ($oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'deleted_by');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(AchievementAward::class, 'achievement_id');
    }
}
