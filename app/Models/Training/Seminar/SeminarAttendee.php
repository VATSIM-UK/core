<?php

namespace App\Models\Training\Seminar;

use App\Models\Model;
use App\Models\Mship\Account;
use App\Observers\Training\SeminarAttendeeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([SeminarAttendeeObserver::class])]
class SeminarAttendee extends Model
{
    use HasFactory;

    protected $table = 'training_seminar_attendees';

    protected $guarded = [];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function seminar(): BelongsTo
    {
        return $this->belongsTo(Seminar::class, 'seminar_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(SeminarInvitation::class, 'invitation_id');
    }
}
