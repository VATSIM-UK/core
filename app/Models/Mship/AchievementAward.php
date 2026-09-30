<?php

namespace App\Models\Mship;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AchievementAward extends Model
{
    use SoftDeletes;

    protected $table = 'mship_achievement_award';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'achievement_id',
        'account_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class)->withTrashed();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
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
}
