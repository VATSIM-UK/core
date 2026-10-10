<?php

namespace App\Models\Training\WaitingList;

use App\Enums\TheoryExamReminderStatus;
use App\Models\Model;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitingListTheoryReminder extends Model
{
    use HasFactory;

    /**
     * Days a student has to attempt their theory exam before being removed.
     */
    public const COMPLETION_WINDOW_DAYS = 7;

    /**
     * Days to wait before checking again after a failed attempt
     */
    public const FAILED_ATTEMPT_RECHECK_WINDOW_DAYS = 7;

    protected $table = 'training_waiting_list_theory_reminders';

    protected $guarded = [];

    protected $casts = [
        'status' => TheoryExamReminderStatus::class,
        'reminded_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'next_check_at' => 'datetime',
        'resolved_at' => 'datetime',
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

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TheoryExamReminderStatus::Pending->value);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('next_check_at', '<=', now());
    }

    public function isPending(): bool
    {
        return $this->status === TheoryExamReminderStatus::Pending;
    }
}
