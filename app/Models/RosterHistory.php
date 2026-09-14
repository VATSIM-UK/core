<?php

namespace App\Models;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RosterHistory extends Model
{
    use HasFactory;

    protected $table = 'roster_history';

    protected $fillable = ['account_id', 'original_created_at', 'original_updated_at', 'removed_by', 'roster_update_id'];

    protected function casts(): array
    {
        return [
            'original_created_at' => 'datetime',
            'original_updated_at' => 'datetime',
            'roster_update_id' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'removed_by');
    }

    public function rosterUpdate(): BelongsTo
    {
        return $this->belongsTo(RosterUpdate::class, 'roster_update_id');
    }
}
