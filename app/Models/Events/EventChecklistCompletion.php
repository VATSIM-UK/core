<?php

namespace App\Models\Events;

use App\Enums\EventChecklistItem;
use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventChecklistCompletion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'item',
        'account_id',
        'completed_at',
    ];

    protected $casts = [
        'item' => EventChecklistItem::class,
        'completed_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
