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

    public const SHORT_NOTICE_THRESHOLD_HOURS = 84;

    public const ADMISSIONS_CLOSE_MINUTES = 30;

    /** Training groups that support seminars. */
    public const ALLOWED_TRAINING_GROUPS = [
        'OBS to S1 Training',
        'P1 Training',
        'P2 Training',
        'P3 Training',
        'P4 Training',
    ];

    /**
     * Email subjects and views used for each training group's seminar emails.
     */
    public const SEMINAR_EMAIL_COPY = [
        'OBS to S1 Training' => [
            'invitation_subject' => 'OBS > S1 Seminar Invitation',
            'short_notice_subject' => 'OBS > S1 Seminar Invitation',
            'invitation_view' => 'emails.training.seminar_invitation',
            'theory_exam_subject' => 'OBS > S1 Seminar - Theory Exam Required',
            'theory_exam_view' => 'emails.training.seminar_theory_exam_reminder',
        ],
        'P1 Training' => [
            'invitation_subject' => 'Seminar Invitation',
            'short_notice_subject' => 'Seminar Invitation - Short Notice',
            'invitation_view' => 'emails.training.seminar_invitation_pilot',
            'theory_exam_subject' => 'PTD - Theory Exam Required',
            'theory_exam_view' => 'emails.training.seminar_theory_exam_reminder_pilot',
        ],
        'P2 Training' => [
            'invitation_subject' => 'Seminar Invitation',
            'short_notice_subject' => 'Seminar Invitation - Short Notice',
            'invitation_view' => 'emails.training.seminar_invitation_pilot',
            'theory_exam_subject' => 'PTD - Theory Exam Required',
            'theory_exam_view' => 'emails.training.seminar_theory_exam_reminder_pilot',
        ],
        'P3 Training' => [
            'invitation_subject' => 'Seminar Invitation',
            'short_notice_subject' => 'Seminar Invitation - Short Notice',
            'invitation_view' => 'emails.training.seminar_invitation_pilot',
            'theory_exam_subject' => 'PTD - Theory Exam Required',
            'theory_exam_view' => 'emails.training.seminar_theory_exam_reminder_pilot',
        ],
        'P4 Training' => [
            'invitation_subject' => 'Seminar Invitation',
            'short_notice_subject' => 'Seminar Invitation - Short Notice',
            'invitation_view' => 'emails.training.seminar_invitation_pilot',
            'theory_exam_subject' => 'PTD - Theory Exam Required',
            'theory_exam_view' => 'emails.training.seminar_theory_exam_reminder_pilot',
        ],
    ];

    /**
     * TODO: This is currently hardcoded to the OBS > S1 mentors channel. If seminars are
     *       created for other training groups, capture the mentors channel ID for each
     *       training group and resolve the channel relevant to this seminar instead.
     */
    public const MENTORS_DISCORD_CHANNEL_ID = '705818534885720176'; // TGNC Mentors Channel

    protected $table = 'training_seminars';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'closed_at' => 'datetime',
        'invitation_expiry_hours' => 'integer',
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

    /**
     * The latest moment an invitation may still be sent or expire
     */
    public function admissionsCloseAt()
    {
        return $this->startsAt()->subMinutes(self::ADMISSIONS_CLOSE_MINUTES);
    }

    public function isSendingCutoffReached(): bool
    {
        if ($this->closed_at !== null) {
            return true;
        }

        return now()->greaterThanOrEqualTo($this->admissionsCloseAt());
    }

    public function isShortNotice(): bool
    {
        return $this->startsAt()->lessThanOrEqualTo(now()->addHours(self::SHORT_NOTICE_THRESHOLD_HOURS));
    }

    public static function seminarEmailCopyFor(?string $trainingGroup): array
    {
        return self::SEMINAR_EMAIL_COPY[$trainingGroup] ?? self::SEMINAR_EMAIL_COPY['OBS to S1 Training'];
    }

    public function seminarEmailCopy(): array
    {
        return self::seminarEmailCopyFor($this->training_group);
    }

    public function startsAt()
    {
        return $this->date->copy()->setTimeFromTimeString($this->from);
    }

    public function mentorsDiscordChannelId(): string
    {
        return self::MENTORS_DISCORD_CHANNEL_ID;
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
