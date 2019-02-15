<?php

namespace App\Models\VisitTransfer;

use App\Events\VisitTransfer\ApplicationAccepted;
use App\Events\VisitTransfer\ApplicationCompleted;
use App\Events\VisitTransfer\ApplicationExpired;
use App\Events\VisitTransfer\ApplicationRejected;
use App\Events\VisitTransfer\ApplicationSubmitted;
use App\Events\VisitTransfer\ApplicationUnderReview;
use App\Events\VisitTransfer\ApplicationWithdrawn;
use App\Exceptions\VisitTransfer\Application\ApplicationAlreadySubmittedException;
use App\Exceptions\VisitTransfer\Application\ApplicationCannotBeExpiredException;
use App\Exceptions\VisitTransfer\Application\ApplicationCannotBeWithdrawnException;
use App\Exceptions\VisitTransfer\Application\ApplicationNotAcceptedException;
use App\Exceptions\VisitTransfer\Application\ApplicationNotRejectableException;
use App\Exceptions\VisitTransfer\Application\ApplicationNotUnderReviewException;
use App\Exceptions\VisitTransfer\Application\AttemptingToTransferToNonTrainingFacilityException;
use App\Exceptions\VisitTransfer\Application\CheckOutcomeAlreadySetException;
use App\Exceptions\VisitTransfer\Application\DuplicateRefereeException;
use App\Exceptions\VisitTransfer\Application\FacilityHasNoCapacityException;
use App\Exceptions\VisitTransfer\Application\TooManyRefereesException;
use App\Models\Model;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use App\Notifications\Mship\SlackInvitation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Malahierba\PublicId\PublicId;

/**
 * App\Models\VisitTransfer\Application
 *
 * @property int $id
 * @property int $type
 * @property string|null $training_team
 * @property int $account_id
 * @property int|null $facility_id
 * @property int $training_required
 * @property int $statement_required
 * @property int $references_required
 * @property int $should_perform_checks
 * @property int|null $check_outcome_90_day
 * @property int|null $check_outcome_50_hours
 * @property int $will_auto_accept
 * @property string|null $statement
 * @property int $status
 * @property string|null $status_note
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $submitted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \App\Models\VisitTransfer\Facility|null $facility
 * @property-read mixed $facility_name
 * @property-read mixed $is_accepted
 * @property-read mixed $is_atc
 * @property-read mixed $is_closed
 * @property-read mixed $is_completed
 * @property-read mixed $is_editable
 * @property-read mixed $is_in_progress
 * @property-read mixed $is_lapsed
 * @property-read mixed $is_not_editable
 * @property-read mixed $is_open
 * @property-read mixed $is_pending_references
 * @property-read mixed $is_pilot
 * @property-read mixed $is_rejected
 * @property-read mixed $is_submitted
 * @property-read mixed $is_transfer
 * @property-read mixed $is_under_review
 * @property-read mixed $is_withdrawable
 * @property-read mixed $is_visit
 * @property-read mixed $number_references_required_relative
 * @property-read mixed $potential_facilities
 * @property-read string $public_id
 * @property-read mixed $references_accepted
 * @property-read mixed $references_not_written
 * @property-read mixed $references_rejected
 * @property-read mixed $references_under_review
 * @property-read mixed $requires_action
 * @property-read mixed $status_string
 * @property-read mixed $type_string
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VisitTransfer\Reference[] $referees
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application closed()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application notStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application ofType($type)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\VisitTransfer\Application onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application open()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application status($status)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application statusIn($stati)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application statusNotIn($stati)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application submitted()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application transfer()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application underReview()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application visit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereCheckOutcome50Hours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereCheckOutcome90Day($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereFacilityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereReferencesRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereShouldPerformChecks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereStatementRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereStatusNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereTrainingRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereTrainingTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Application whereWillAutoAccept($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\VisitTransfer\Application withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\VisitTransfer\Application withoutTrashed()
 * @mixin \Eloquent
 */
class Application extends Model
{
    use PublicId, SoftDeletes;

    protected static $public_id_salt = 'vatsim-uk-visiting-transfer-applications';
    protected static $public_id_min_length = 8;
    protected static $public_id_alphabet = 'upper_alphanumeric';
    protected $trackedEvents = ['created', 'updated', 'deleted', 'restored'];

    protected $table = 'vt_application';
    protected $fillable = [
        'type',
        'training_team',
        'account_id',
        'facility_id',
        'statement',
        'status',
        'expires_at',
    ];
    public $timestamps = true;
    protected $dates = [
        'expires_at',
        'submitted_at',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'check_outcome_90_day' => 'boolean',
        'check_outcome_50_hours' => 'boolean',
    ];

    const TYPE_VISIT = 10;
    const TYPE_TRANSFER = 40;

    const STATUS_IN_PROGRESS = 10; // Member hasn't yet submitted application formally.
    const STATUS_WITHDRAWN = 15; // Application has been withdrawn.
    const STATUS_EXPIRED = 16; // Application expired after 1 hour.
    const STATUS_SUBMITTED = 30; // Member has formally submitted application.
    const STATUS_UNDER_REVIEW = 50; // References and checks have been completed.
    const STATUS_ACCEPTED = 60; // Application has been accepted by staff
    const STATUS_PENDING_CERT = 70; // Application has been completed, but is pending a cert update to be formally complete.
    const STATUS_COMPLETED = 90; // Application has been formally completed, visit/transfer complete.
    const STATUS_LAPSED = 93; // Application has lapsed.
    const STATUS_CANCELLED = 96; // Application has been cancelled
    const STATUS_REJECTED = 99; // Application has been rejected by staff

    public static $APPLICATION_IS_CONSIDERED_EDITABLE = [
        self::STATUS_IN_PROGRESS,
    ];

    public static $APPLICATION_IS_CONSIDERED_OPEN = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_ACCEPTED,
    ];

    public static $APPLICATION_IS_CONSIDERED_CLOSED = [
        self::STATUS_COMPLETED,
        self::STATUS_LAPSED,
        self::STATUS_WITHDRAWN,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
        self::STATUS_REJECTED,
    ];

    public static $APPLICATION_REQUIRES_ACTION = [
        self::STATUS_IN_PROGRESS,
    ];

    public static $APPLICATION_IS_CONSIDERED_WITHDRAWABLE = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_SUBMITTED,
    ];

    public function __construct(array $attributes = [])
    {
        $attributes['expires_at'] = \Carbon\Carbon::now()->addHour();

        parent::__construct($attributes);
    }

    /** All Laravel scopes **/
    public static function scopeOfType($query, $type)
    {
        return $query->where('type', '=', $type);
    }

    public static function scopeVisit($query)
    {
        return $query->ofType(self::TYPE_VISIT);
    }

    public static function scopeTransfer($query)
    {
        return $query->ofType(self::TYPE_TRANSFER);
    }

    public static function scopeStatus($query, $status)
    {
        return $query->statusIn([$status]);
    }

    public static function scopeNotStatus($query, $status)
    {
        return $query->statusNotIn([$status]);
    }

    public static function scopeStatusIn($query, array $stati)
    {
        return $query->whereIn('status', $stati);
    }

    public static function scopeStatusNotIn($query, array $stati)
    {
        return $query->whereNotIn('status', $stati);
    }

    public static function scopeOpen($query)
    {
        return $query->statusIn(self::$APPLICATION_IS_CONSIDERED_OPEN);
    }

    public static function scopeClosed($query)
    {
        return $query->statusIn(self::$APPLICATION_IS_CONSIDERED_CLOSED);
    }

    public static function scopeSubmitted($query)
    {
        return $query->status(self::STATUS_SUBMITTED);
    }

    public static function scopeUnderReview($query)
    {
        return $query->status(self::STATUS_UNDER_REVIEW);
    }

    /** All Laravel relationships */
    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id', 'id');
    }

    public function facility()
    {
        return $this->belongsTo(\App\Models\VisitTransfer\Facility::class);
    }

    public function referees()
    {
        return $this->hasMany(\App\Models\VisitTransfer\Reference::class);
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Mship\Account\Note::class, 'attachment');
    }

    /** All Laravel magic attributes **/
    public function getIsPilotAttribute()
    {
        return strcasecmp($this->attributes['training_team'], 'pilot') == 0;
    }

    public function getIsAtcAttribute()
    {
        return strcasecmp($this->attributes['training_team'], 'atc') == 0;
    }

    public function setStatementAttribute($statement)
    {
        $this->attributes['statement'] = trim($statement);
    }

    public function getPotentialFacilitiesAttribute()
    {
        if ($this->facility) {
            return collect([]);
        }

        if ($this->is_pilot) {
            return Facility::pilot()->isOpen()->public()->get();
        }

        if ($this->is_visit) {
            return Facility::atc()->isOpen()->public()->canVisit()->get();
        }

        return Facility::atc()->isOpen()->public()->canTransfer()->get();
    }

    public function getIsOpenAttribute()
    {
        return $this->isStatusIn(self::$APPLICATION_IS_CONSIDERED_OPEN);
    }

    public function getIsEditableAttribute()
    {
        return $this->isStatusIn(self::$APPLICATION_IS_CONSIDERED_EDITABLE);
    }

    public function getIsNotEditableAttribute()
    {
        return $this->isStatusNotIn(self::$APPLICATION_IS_CONSIDERED_EDITABLE);
    }

    public function getRequiresActionAttribute()
    {
        return $this->isStatusIn(self::$APPLICATION_REQUIRES_ACTION);
    }

    public function getIsClosedAttribute()
    {
        return $this->isStatusIn(self::$APPLICATION_IS_CONSIDERED_CLOSED);
    }

    public function getIsWithdrawableAttribute()
    {
        return $this->isStatusIn(self::$APPLICATION_IS_CONSIDERED_WITHDRAWABLE);
    }

    public function getIsInProgressAttribute()
    {
        return $this->isStatus(self::STATUS_IN_PROGRESS);
    }

    public function getIsSubmittedAttribute()
    {
        return $this->isStatus(self::STATUS_SUBMITTED);
    }

    public function getIsWithdrawnAttribute()
    {
        return $this->isStatus(self::STATUS_WITHDRAWN);
    }

    public function getIsPendingReferencesAttribute()
    {
        return $this->references_not_written->count() > 0;
    }

    public function getIsUnderReviewAttribute()
    {
        return $this->isStatus(self::STATUS_UNDER_REVIEW);
    }

    public function getIsAcceptedAttribute()
    {
        return $this->isStatus(self::STATUS_ACCEPTED);
    }

    public function getIsCompletedAttribute()
    {
        return $this->isStatusIn([self::STATUS_PENDING_CERT, self::STATUS_COMPLETED]);
    }

    public function getIsLapsedAttribute()
    {
        return $this->isStatus(self::STATUS_LAPSED);
    }

    public function getIsRejectedAttribute()
    {
        return $this->isStatus(self::STATUS_REJECTED);
    }

    public function getStatusStringAttribute()
    {
        switch ($this->attributes['status']) {
            case self::STATUS_IN_PROGRESS:
                return 'In Progress';
            case self::STATUS_WITHDRAWN:
                return 'Withdrawn';
            case self::STATUS_EXPIRED:
                return 'Expired Automatically';
            case self::STATUS_SUBMITTED:
                return 'Submitted';
            case self::STATUS_UNDER_REVIEW:
                return 'Under Review';
            case self::STATUS_ACCEPTED:
                return 'Accepted';
            case self::STATUS_COMPLETED:
                return 'Completed';
            case self::STATUS_LAPSED:
                return 'Lapsed';
            case self::STATUS_CANCELLED:
                return 'Cancelled';
            case self::STATUS_REJECTED:
                return 'Rejected';
        }
    }

    public function getIsVisitAttribute()
    {
        return $this->type == self::TYPE_VISIT;
    }

    public function getIsTransferAttribute()
    {
        return $this->type == self::TYPE_TRANSFER;
    }

    public function getTrainingTeamAttribute()
    {
        if (!$this->exists) {
            return 'Unknown';
        }

        if ($this->attributes['training_team'] == 'atc') {
            return 'ATC';
        }

        return ucfirst($this->attributes['training_team']);
    }

    public function getTypeStringAttribute()
    {
        if ($this->is_visit) {
            return $this->training_team.' Visit';
        }

        return $this->training_team.' Transfer';
    }

    public function getNumberReferencesRequiredRelativeAttribute()
    {
        return $this->references_required - $this->referees->count();
    }

    public function getReferencesNotWrittenAttribute()
    {
        return $this->referees()->pending()->get();
    }

    public function getReferencesUnderReviewAttribute()
    {
        return $this->referees()->underReview()->get();
    }

    public function getReferencesAcceptedAttribute()
    {
        return $this->referees()->accepted()->get();
    }

    public function getReferencesRejectedAttribute()
    {
        return $this->referees()->rejected()->get();
    }

    public function getFacilityNameAttribute()
    {
        return $this->facility ? $this->facility->name : 'Not selected';
    }

    /** Business logic. */
    public function isStatus($status)
    {
        return $this->status == $status;
    }

    public function isStatusIn($stati)
    {
        return in_array($this->status, $stati);
    }

    public function isStatusNotIn($stati)
    {
        return !$this->isStatusIn($stati);
    }

    public function setFacility(Facility $facility)
    {
        $this->guardAgainstTransferringToANonTrainingFacility($facility);

        $this->guardAgainstApplyingToAFacilityWithNoCapacity($facility);

        $this->training_required = $facility->training_required;
        $this->statement_required = $facility->stage_statement_enabled;
        $this->references_required = $facility->stage_reference_enabled ? $facility->stage_reference_quantity : 0;
        $this->should_perform_checks = $facility->stage_checks;
        $this->will_auto_accept = $facility->auto_acceptance;

        $facility->applications()->save($this);
    }

    public function addReferee(Account $refereeAccount, $email, $relationship)
    {
        $this->guardAgainstDuplicateReferee($refereeAccount);

        $this->guardAgainstTooManyReferees();

        $reference = new Reference([
            'email' => $email,
            'relationship' => $relationship,
        ]);

        $reference->account()->associate($refereeAccount);

        $this->referees()->save($reference);
    }

    public function setStatement($statement)
    {
        $this->statement = trim(strip_tags($statement));

        $this->save();
    }

    public function withdraw()
    {
        $this->guardAgainstInvalidWithdrawal();

        $this->attributes['status'] = self::STATUS_WITHDRAWN;
        $this->save();

        // Cancel references
        foreach ($this->referees as $reference) {
            $reference->cancel();
        }

        event(new ApplicationWithdrawn($this));

        if ($this->facility) {
            $this->facility->addTrainingSpace();
        }
    }

    public function expire()
    {
        $this->guardAgainstInvalidExpiry();

        $this->attributes['status'] = self::STATUS_EXPIRED;
        $this->save();

        // Cancel references
        foreach ($this->referees as $reference) {
            $reference->cancel();
        }

        event(new ApplicationExpired($this));

        if ($this->facility) {
            $this->facility->addTrainingSpace();
        }

        if ($this->is_transfer) {
            $this->account->removeState(State::findByCode('TRANSFERRING'));
        }
    }

    public function lapse()
    {
        $this->status = self::STATUS_LAPSED;
        $this->save();

        if ($this->facility) {
            $this->facility->addTrainingSpace();
        }

        if ($this->is_transfer) {
            $this->account->removeState(State::findByCode('TRANSFERRING'));
        }

        foreach ($this->referees as $reference) {
            $reference->delete();
        }
    }

    public function submit()
    {
        $this->guardAgainstInvalidSubmission();

        $this->attributes['submitted_at'] = Carbon::now();
        $this->attributes['status'] = self::STATUS_SUBMITTED;
        $this->save();

        event(new ApplicationSubmitted($this));

        $this->facility->removeTrainingSpace();
    }

    public function markAsUnderReview($staffReason = null, Account $actor = null)
    {
        $this->attributes['status'] = self::STATUS_UNDER_REVIEW;
        $this->save();

        if ($staffReason) {
            $noteContent = 'VT Application for '.$this->type_string.' '.$this->facility->name." was progressed to 'Under Review'.\n".$staffReason;
            $note = $this->account->addNote('visittransfer', $noteContent, $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ApplicationUnderReview($this));
    }

    public function reject($publicReason = 'No reason was provided.', $staffReason = null, Account $actor = null)
    {
        $this->guardAgainstNonRejectableApplication();

        $this->status = self::STATUS_REJECTED;
        $this->status_note = $publicReason;
        $this->save();

        if ($staffReason) {
            $noteContent = 'VT Application for '.$this->type_string.' '.$this->facility->name." was rejected.\n".$staffReason;
            $note = $this->account->addNote('visittransfer', $noteContent, $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        // Cancel any outstanding references
        foreach ($this->referees as $reference) {
            $reference->cancel();
        }

        event(new ApplicationRejected($this));

        if ($this->is_transfer) {
            $this->account->removeState(State::findByCode('TRANSFERRING'));
        }
    }

    public function accept($staffComment = null, Account $actor = null)
    {
        $this->guardAgainstNonUnderReviewApplication();

        $this->status = self::STATUS_ACCEPTED;
        $this->save();

        $noteContent = 'VT Application for '.$this->type_string.' '.$this->facility->name." was accepted.\n";

        if ($staffComment) {
            $noteContent .= $staffComment;
        }

        $note = $this->account->addNote('visittransfer', $noteContent, $actor, $this);
        $this->notes()->save($note);
        // TODO: Investigate why this is required!!!!

        if ($this->is_visit) {
            $this->account->addState(State::findByCode('VISITING'));
        }

        if ($this->is_transfer) {
            $this->account->addState(State::findByCode('TRANSFERRING'));
        }

        // Cancel any outstanding references
        foreach ($this->referees as $reference) {
            $reference->cancel();
        }

        $this->account->notify((new SlackInvitation())->delay(Carbon::now()->addDays(3)));

        event(new ApplicationAccepted($this));
    }

    public function complete($staffComment = null, Account $actor = null)
    {
        $this->guardAgainstNonAcceptedApplication();

        $this->status = self::STATUS_COMPLETED;
        //        $this->status = ($this->is_visit ? self::STATUS_COMPLETED : self::STATUS_PENDING_CERT);
        $this->save();

        $noteContent = 'VT Application for '.$this->type_string.' '.$this->facility->name." was completed.\n";

        if ($staffComment) {
            $noteContent .= $staffComment;
        }

        $note = $this->account->addNote('visittransfer', $noteContent, $actor, $this);
        $this->notes()->save($note);

        event(new ApplicationCompleted($this));
    }

    public function setCheckOutcome($check, $outcome)
    {
        // $this->guardAgainstDuplicateCheckOutcomeSubmission($check);

        $columnName = 'check_outcome_'.$check;
        $this->{$columnName} = (int) $outcome;
        $this->save();
    }

    public function settingToggle($setting)
    {
        switch ($setting) {
            case 'training_required':
                return $this->settingToggleGenericBoolean('training_required');
            case 'statement_required':
                $this->statement = null;

                return $this->settingToggleGenericBoolean('statement_required');
            case 'references_required':
                return $this->settingToggleReferencesRequired();
            case 'should_perform_checks':
                return $this->settingToggleGenericBoolean('should_perform_checks');
            case 'will_auto_accept':
                return $this->settingToggleGenericBoolean('will_auto_accept');
        }
    }

    private function settingToggleReferencesRequired()
    {
        if ($this->references_required == 0) {
            $this->references_required = $this->facility->stage_reference_enabled ? $this->facility->stage_reference_quantity : 0;

            return $this->save();
        }

        foreach ($this->referees as $reference) {
            $reference->delete();
        }

        $this->references_required = 0;

        return $this->save();
    }

    private function settingToggleGenericBoolean($columnName)
    {
        if ($this->{$columnName} === 1) {
            $this->{$columnName} = 0;

            return $this->save();
        }

        $this->{$columnName} = 1;

        return $this->save();
    }

    public function check90DayQualification()
    {
        if (!$this->submitted_at) {
            return false;
        }

        $currentATCQualification = $this->account->qualification_atc;
        $application90DayCutOff = $this->submitted_at->subDays(90);

        return $currentATCQualification->pivot->created_at->lt($application90DayCutOff);
    }

    public function check50Hours()
    {
        $qualificationId = $this->account->qualification_atc->id;
        $timeOnline = $this->account->networkDataAtc()->forQualificationId($qualificationId)->offline()->sum('minutes_online');

        return $timeOnline >= (50 * 60);
    }

    /** Statistics */
    public static function statisticTotal()
    {
        return Cache::remember('VT_APPLICATIONS_STATISTICS_TOTAL', 1, function () {
            return self::count();
        });
    }

    public static function statisticOpenNotInProgress()
    {
        return Cache::remember('VT_APPLICATIONS_STATISTICS_OPEN_NOT_IN_PROGRESS', 1, function () {
            return self::statusIn(self::$APPLICATION_IS_CONSIDERED_OPEN)->where('status', '!=', self::STATUS_IN_PROGRESS)->count();
        });
    }

    public static function statisticUnderReview()
    {
        return Cache::remember('VT_APPLICATIONS_STATISTICS_UNDER_REVIEW', 1, function () {
            return self::underReview()->count();
        });
    }

    public static function statisticAccepted()
    {
        return Cache::remember('VT_APPLICATIONS_STATISTICS_ACCEPTED', 1, function () {
            return self::status(self::STATUS_ACCEPTED)->count();
        });
    }

    public static function statisticClosed()
    {
        return Cache::remember('VT_APPLICATIONS_STATISTICS_CLOSED', 1, function () {
            return self::closed()->count();
        });
    }

    /** Guards */
    private function guardAgainstTransferringToANonTrainingFacility(Facility $requestedFacility)
    {
        if ($this->is_transfer && $requestedFacility->training_required == 0) {
            throw new AttemptingToTransferToNonTrainingFacilityException($requestedFacility);
        }
    }

    private function guardAgainstApplyingToAFacilityWithNoCapacity(Facility $requestedFacility)
    {
        if ($requestedFacility->training_required == 1 && $requestedFacility->training_spaces === 0) {
            throw new FacilityHasNoCapacityException($requestedFacility);
        }
    }

    private function guardAgainstDuplicateReferee($refereeAccount)
    {
        $checkContains = $this->referees->filter(function ($referee) use ($refereeAccount) {
            return $referee->account_id == $refereeAccount->id;
        })->count() > 0;

        if ($checkContains) {
            throw new DuplicateRefereeException($refereeAccount);
        }
    }

    private function guardAgainstTooManyReferees()
    {
        if ($this->number_references_required_relative == 0) {
            throw new TooManyRefereesException($this);
        }
    }

    private function guardAgainstInvalidSubmission()
    {
        if ($this->is_submitted) {
            throw new ApplicationAlreadySubmittedException($this);
        }
    }

    private function guardAgainstNonRejectableApplication()
    {
        if ($this->is_under_review || $this->is_submitted) {
            return true;
        }

        throw new ApplicationNotRejectableException($this);
    }

    private function guardAgainstNonUnderReviewApplication()
    {
        if ($this->is_under_review) {
            return;
        }

        throw new ApplicationNotUnderReviewException($this);
    }

    private function guardAgainstNonAcceptedApplication()
    {
        if ($this->is_accepted) {
            return;
        }

        throw new ApplicationNotAcceptedException($this);
    }

    private function guardAgainstDuplicateCheckOutcomeSubmission($check)
    {
        $tableColumnName = 'check_outcome_'.$check;
        if ($this->{$tableColumnName} !== null) {
            throw new CheckOutcomeAlreadySetException($this, $check);
        }
    }

    private function guardAgainstInvalidWithdrawal()
    {
        if ($this->is_withdrawable) {
            return;
        }

        throw new ApplicationCannotBeWithdrawnException($this);
    }

    private function guardAgainstInvalidExpiry()
    {
        if ($this->is_in_progress) {
            return;
        }

        throw new ApplicationCannotBeExpiredException($this);
    }
}
