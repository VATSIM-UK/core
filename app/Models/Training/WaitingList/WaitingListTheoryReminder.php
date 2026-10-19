<?php

namespace App\Models\Training\WaitingList;

use App\Enums\SeminarTheoryReminderStatus;
use App\Models\Model;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitingListTheoryReminder extends Model
{
    protected $table = 'training_waiting_list_theory_reminders';

    protected $guarded = [];

    protected $casts = [
        'reminded_at' => 'datetime',
        'status' => SeminarTheoryReminderStatus::class,
    ];

    public function waitingListAccount(): BelongsTo
    {
        return $this->belongsTo(WaitingListAccount::class, 'waiting_list_account_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function seminar(): BelongsTo
    {
        return $this->belongsTo(Seminar::class, 'seminar_id');
    }

    public function actionedSeminar(): BelongsTo
    {
        return $this->belongsTo(Seminar::class, 'actioned_seminar_id');
    }

    public function isForSeminar(int $seminarId): bool
    {
        return $this->seminar_id !== null && (int) $this->seminar_id === $seminarId;
    }

    public function isForAnotherSeminar(int $seminarId): bool
    {
        return $this->seminar_id !== null && (int) $this->seminar_id !== $seminarId;
    }

    public function markPassedAt(Seminar $seminar): void
    {
        if ($this->status === SeminarTheoryReminderStatus::Removed) {
            return;
        }

        if ($this->status === SeminarTheoryReminderStatus::Passed && $this->actioned_seminar_id !== null) {
            return;
        }

        $this->update([
            'status' => SeminarTheoryReminderStatus::Passed,
            'actioned_seminar_id' => $seminar->id,
        ]);
    }

    public function markRemovedAt(Seminar $seminar): void
    {
        if ($this->status === SeminarTheoryReminderStatus::Passed) {
            return;
        }

        $this->update([
            'status' => SeminarTheoryReminderStatus::Removed,
            'actioned_seminar_id' => $seminar->id,
        ]);
    }
}
