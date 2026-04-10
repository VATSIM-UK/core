<?php

namespace App\Models\Training\TrainingPlace;

use App\Models\Cts\Session as CtsSession;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Observers\Training\TrainingPlaceObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([TrainingPlaceObserver::class])]
class TrainingPlace extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPlace\TrainingPlaceFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    /**
     * Hours after creation during which scheduled availability checks are skipped (not including on-leave checks).
     */
    public const AVAILABILITY_CHECK_GRACE_PERIOD_HOURS = 48;

    protected $guarded = [];

    public function waitingListAccount(): BelongsTo
    {
        return $this->belongsTo(WaitingListAccount::class, 'waiting_list_account_id')
            ->withTrashed();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Transitional accessor to resolve the student account from the new direct FK
     * (preferred) or the legacy waiting-list relationship (fallback).
     */
    public function studentAccount(): ?Account
    {
        return $this->account ?? $this->waitingListAccount?->account;
    }

    public function trainingPosition(): BelongsTo
    {
        return $this->belongsTo(TrainingPosition::class, 'training_position_id');
    }

    public function availabilityChecks(): HasMany
    {
        return $this->hasMany(AvailabilityCheck::class);
    }

    public function availabilityWarnings(): HasMany
    {
        return $this->hasMany(AvailabilityWarning::class);
    }

    public function leaveOfAbsences(): HasMany
    {
        return $this->hasMany(TrainingPlaceLeaveOfAbsence::class);
    }

    public function isOnLeaveOfAbsence()
    {
        return $this->leaveOfAbsences()->current()->exists();
    }

    public function availabilityCheckGracePeriodEndsAt(): Carbon
    {
        return $this->created_at->copy()->addHours(self::AVAILABILITY_CHECK_GRACE_PERIOD_HOURS);
    }

    public function isWithinAvailabilityCheckGracePeriod(): bool
    {
        return now()->lt($this->availabilityCheckGracePeriodEndsAt());
    }

    public function currentLeaveOfAbsence()
    {
        return $this->leaveOfAbsences()->current()->first();
    }

    public function deletePendingSessionRequests(): void
    {
        $this->loadMissing([
            'trainingPosition',
            'waitingListAccount.account',
        ]);

        $account = $this->waitingListAccount?->account;
        $member = $account?->member;

        if (! $member) {
            return;
        }

        $callsign = $this->trainingPosition?->cts_primary_position;

        if (! is_string($callsign) || trim($callsign) === '') {
            return;
        }

        $callsign = trim($callsign);

        CtsSession::query()
            ->where('student_id', $member->id)
            ->where('position', $callsign)
            ->whereNull('taken_date')
            ->where('session_done', 0)
            ->delete();
    }

    public function revokeTrainingPlace(string $reason, Account $admin): void
    {
        $this->waitingListAccount->account->addNote('training', "Training place revoked on {$this->trainingPosition->position->callsign}. Reason: {$reason}", $admin->id);
        $this->delete();
    }
}
