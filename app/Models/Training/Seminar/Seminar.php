<?php

namespace App\Models\Training\Seminar;

use App\Enums\SeminarInvitationStatus;
use App\Models\Model;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Observers\Training\SeminarObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([SeminarObserver::class])]
class Seminar extends Model
{
    use HasFactory;

    protected $table = 'training_seminars';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'closed_at' => 'datetime',
        'invitation_expiry_days' => 'integer',
        'automatic_invitations_enabled' => 'boolean',
    ];

    public function waitingList(): BelongsTo
    {
        return $this->belongsTo(WaitingList::class, 'waiting_list_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(SeminarInvitation::class, 'seminar_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(SeminarAttendee::class, 'seminar_id');
    }

    public function waitingListAccounts(): HasMany
    {
        return $this->hasMany(WaitingListAccount::class, 'list_id', 'waiting_list_id')
            ->whereNull('deleted_at');
    }

    public function hasStarted(): bool
    {
        return now()->greaterThanOrEqualTo(
            $this->date->copy()->setTimeFromTimeString((string) $this->from)
        );
    }

    public function isClosed(): bool
    {
        if ($this->closed_at !== null) {
            return true;
        }

        return $this->hasStarted();
    }

    public function isSendingCutoffReached(): bool
    {
        if ($this->closed_at !== null) {
            return true;
        }

        $cutoff = $this->startsAt()->subDays($this->invitation_expiry_days);

        return now()->greaterThanOrEqualTo($cutoff);
    }

    public function startsAt()
    {
        return $this->date->copy()->setTimeFromTimeString($this->from);
    }

    public function spacesRemaining(): int
    {
        return $this->capacity - $this->attendees()->count() - $this->invitations()->where('status', SeminarInvitationStatus::Sent->value)->count();
    }

    public function canInvite(): bool
    {
        return ! $this->isSendingCutoffReached() && $this->spacesRemaining() > 0;
    }
}
