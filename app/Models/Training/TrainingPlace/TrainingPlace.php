<?php

namespace App\Models\Training\TrainingPlace;

use App\Models\Cts\CancelReason;
use App\Models\Cts\Session as CtsSession;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Observers\Training\TrainingPlaceObserver;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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

    protected $fillable = [
        'account_id',
        'waiting_list_account_id',
        'trainable_type',
        'trainable_id',
    ];

    public function waitingListAccount(): BelongsTo
    {
        return $this->belongsTo(WaitingListAccount::class, 'waiting_list_account_id')
            ->withTrashed();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function trainable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function trainingPosition(): Attribute
    {
        return Attribute::make(
            get: fn (): ?TrainingPosition => $this->trainable instanceof TrainingPosition ? $this->trainable : null,
        );
    }

    protected function qualification(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Qualification => $this->trainable instanceof Qualification ? $this->trainable : null,
        );
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(get: function (): string {
            $trainable = $this->trainable;

            if ($trainable instanceof TrainingPosition) {
                return $trainable->position?->callsign
                    ?? collect($trainable->cts_positions)->filter()->first()
                    ?? "Position {$trainable->id}";
            }

            if ($trainable instanceof Qualification) {
                return $trainable->name_long ?? $trainable->name_small ?? $trainable->code ?? "Qualification {$trainable->id}";
            }

            return 'Unknown';
        });
    }

    /**
     * The CTS position callsigns associated with this training place's trainable.
     *
     * @return array<int, string>
     */
    public function trainableCtsPositions(): array
    {
        return app(MentorPermissionService::class)->getCtsCallsignsForMentorable($this->trainable);
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

    public function hasExamCancellations(): bool
    {
        $trainingPosition = $this->trainingPosition;

        if (! $trainingPosition) {
            return false;
        }

        $position = $trainingPosition->exam_callsign ?? $trainingPosition->position?->callsign;

        if (! $position) {
            return false;
        }

        $member = $this->account->member;

        if (! $member) {
            return false;
        }

        return CancelReason::query()
            ->select('cancel_reason.*')
            ->join('exam_book', 'cancel_reason.sesh_id', '=', 'exam_book.id')
            ->where('cancel_reason.sesh_type', 'EX')
            ->where('exam_book.position_1', $position)
            ->where('exam_book.student_id', $member->id)
            ->exists();
    }

    public function deletePendingSessionRequests(): void
    {
        $this->loadMissing([
            'trainable',
            'account',
        ]);

        $member = $this->account->member;

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
        $this->account->addNote('training', "Training place revoked on {$this->display_name}. Reason: {$reason}", $admin->id);

        $this->delete();
    }
}
