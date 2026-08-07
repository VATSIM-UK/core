<?php

namespace App\Models\Training\Seminar;

use App\Enums\SeminarInvitationStatus;
use App\Models\Model;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SeminarInvitation extends Model
{
    use HasFactory;

    protected $table = 'training_seminar_invitations';

    protected $guarded = [];

    protected $casts = [
        'status' => SeminarInvitationStatus::class,
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function seminar(): BelongsTo
    {
        return $this->belongsTo(Seminar::class, 'seminar_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function waitingListAccount(): BelongsTo
    {
        return $this->belongsTo(WaitingListAccount::class, 'waiting_list_account_id');
    }

    public function attendee(): HasOne
    {
        return $this->hasOne(SeminarAttendee::class, 'invitation_id', 'id');
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(get: fn () => $this->status->label());
    }

    public function canRespond(): bool
    {
        return $this->status === SeminarInvitationStatus::Sent
            && $this->expires_at->isFuture()
            && ! $this->seminar->isClosed();
    }
}
