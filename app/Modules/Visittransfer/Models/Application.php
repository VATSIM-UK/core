<?php

namespace App\Modules\Visittransfer\Models;

use App\Models\Mship\Account;
use App\Modules\Visittransfer\Events\ApplicationAccepted;
use App\Modules\Visittransfer\Events\ApplicationCompleted;
use App\Modules\Visittransfer\Events\ApplicationRejected;
use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use App\Modules\Visittransfer\Events\ApplicationUnderReview;
use App\Modules\Visittransfer\Events\ApplicationWithdrawn;
use App\Modules\Visittransfer\Exceptions\Application\ApplicationAlreadySubmittedException;
use App\Modules\Visittransfer\Exceptions\Application\ApplicationCannotBeWithdrawnException;
use App\Modules\Visittransfer\Exceptions\Application\ApplicationNotAcceptedException;
use App\Modules\Visittransfer\Exceptions\Application\ApplicationNotUnderReviewException;
use App\Modules\Visittransfer\Exceptions\Application\AttemptingToTransferToNonTrainingFacilityException;
use App\Modules\Visittransfer\Exceptions\Application\CheckOutcomeAlreadySetException;
use App\Modules\Visittransfer\Exceptions\Application\DuplicateRefereeException;
use App\Modules\Visittransfer\Exceptions\Application\FacilityHasNoCapacityException;
use App\Modules\Visittransfer\Exceptions\Application\TooManyRefereesException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Malahierba\PublicId\PublicId;

/**
 * App\Modules\Ais\Models\Fir
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Aerodrome[]  $airfields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Fir\Sector[] $sectors
 */
class Application extends Model
{
    use PublicId, SoftDeletes;

    static protected $public_id_salt       = 'vatsim-uk-visiting-transfer-applications';
    static protected $public_id_min_length = 8;
    static protected $public_id_alphabet   = 'upper_alphanumeric';

    protected $table      = "vt_application";
    protected $fillable   = [
        "type",
        "training_team",
        "account_id",
        "facility_id",
        "statement",
        "status",
    ];
    public    $timestamps = true;
    protected $dates      = [
        "submitted_at",
        "created_at",
        "updated_at"
    ];

    const TYPE_VISIT    = 10;
    const TYPE_TRANSFER = 40;

    const STATUS_IN_PROGRESS  = 10; // Member hasn't yet submitted application formally.
    const STATUS_WITHDRAWN    = 15; // Application has been withdrawn
    const STATUS_SUBMITTED    = 30; // Member has formally submitted application.
    const STATUS_UNDER_REVIEW = 50; // References and checks have been completed.
    const STATUS_ACCEPTED     = 60; // Application has been accepted by staff
    const STATUS_PENDING_CERT = 70; // Application has been completed, but is pending a cert update to be formally complete.
    const STATUS_COMPLETED    = 90; // Application has been formally completed, visit/transfer complete.
    const STATUS_LAPSED       = 93; // Application has lapsed.
    const STATUS_CANCELLED    = 96; // Application has been cancelled
    const STATUS_REJECTED     = 99; // Application has been rejected by staff

    static $APPLICATION_IS_CONSIDERED_EDITABLE = [
        self::STATUS_IN_PROGRESS,
    ];

    static $APPLICATION_IS_CONSIDERED_OPEN = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_ACCEPTED,
    ];

    static $APPLICATION_IS_CONSIDERED_CLOSED = [
        self::STATUS_COMPLETED,
        self::STATUS_LAPSED,
        self::STATUS_WITHDRAWN,
        self::STATUS_CANCELLED,
        self::STATUS_REJECTED,
    ];

    static $APPLICATION_REQUIRES_ACTION = [
        self::STATUS_IN_PROGRESS,
    ];

    /** All Laravel scopes **/
    public static function scopeOfType($query, $type)
    {
        return $query->where("type", "=", $type);
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
        return $query->whereStatus($status);
    }

    public static function scopeNotStatus($query, $status)
    {
        return $query->where("status", "!=", $status);
    }

    public static function scopeStatusIn($query, Array $stati)
    {
        return $query->whereIn("status", $stati);
    }

    public static function scopeStatusNotIn($query, Array $stati)
    {
        return $query->whereNotIn("status", $stati);
    }

    public static function scopeOpen($query)
    {
        return $query->statusIn(self::$APPLICATION_IS_CONSIDERED_OPEN);
    }

    public static function scopeClosed($query)
    {
        return $query->status(self::$APPLICATION_IS_CONSIDERED_CLOSED);
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
        return $this->belongsTo(\App\Models\Mship\Account::class, "account_id", "id");
    }

    public function facility()
    {
        return $this->belongsTo(\App\Modules\Visittransfer\Models\Facility::class);
    }

    public function referees()
    {
        return $this->hasMany(\App\Modules\Visittransfer\Models\Reference::class);
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Mship\Account\Note::class, "attachment");
    }

    /** All Laravel magic attributes **/
    public function getIsPilotAttribute()
    {
        return strcasecmp($this->attributes['training_team'], "pilot") == 0;
    }

    public function getIsAtcAttribute()
    {
        return strcasecmp($this->attributes['training_team'], "atc") == 0;
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
            return Facility::pilot()->get();
        }

        if ($this->is_visit) {
            return Facility::atc()->canVisit()->get();
        }

        return Facility::atc()->canTransfer()->get();
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

    public function getIsInProgressAttribute()
    {
        return $this->isStatus(self::STATUS_IN_PROGRESS);
    }

    public function getIsSubmittedAttribute()
    {
        return $this->isStatus(self::STATUS_SUBMITTED);
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
                return "In Progress";
            case self::STATUS_WITHDRAWN:
                return "Withdrawn";
            case self::STATUS_SUBMITTED:
                return "Submitted";
            case self::STATUS_UNDER_REVIEW:
                return "Under Review";
            case self::STATUS_ACCEPTED:
                return "Accepted";
            case self::STATUS_COMPLETED:
                return "Completed";
            case self::STATUS_LAPSED:
                return "Lapsed";
            case self::STATUS_CANCELLED:
                return "Cancelled";
            case self::STATUS_REJECTED:
                return "Rejected";
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
            return "Unknown";
        }

        if ($this->attributes['training_team'] == 'atc') {
            return "ATC";
        }

        return ucfirst($this->attributes['training_team']);
    }

    public function getTypeStringAttribute()
    {
        if ($this->is_visit) {
            return $this->training_team . " Visit";
        }

        return $this->training_team . " Transfer";
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
        return $this->facility ? $this->facility->name : "Not selected";
    }

    /** Business logic. */
    public function isStatus($status)
    {
        return $this->status == $status;
    }

    public function isStatusIn($stati)
    {
        return in_array($this->attributes['status'], $stati);
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

        $referee = new Reference([
            "email"        => $email,
            "relationship" => $relationship,
        ]);

        $this->referees()->save($referee);

        $refereeAccount->visitTransferReferee()->save($referee);
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

        event(new ApplicationWithdrawn($this));

        if($this->facility){
            $this->facility->addTrainingSpace();
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
            $noteContent = "VT Application for " . $this->type_string . " " . $this->facility->name . " was progressed to 'Under Review'.\n" . $staffReason;
            $note = $this->account->addNote("visittransfer", $noteContent, $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ApplicationUnderReview($this));
    }

    public function reject($publicReason = "No reason was provided.", $staffReason = null, Account $actor = null)
    {
        $this->guardAgainstNonUnderReviewApplication();

        $this->status = self::STATUS_REJECTED;
        $this->status_note = $publicReason;
        $this->save();

        if ($staffReason) {
            $noteContent = "VT Application for " . $this->type_string . " " . $this->facility->name . " was rejected.\n" . $staffReason;
            $note = $this->account->addNote("visittransfer", $noteContent, $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ApplicationRejected($this));
    }

    public function accept($staffComment = null, Account $actor = null)
    {
        $this->guardAgainstNonUnderReviewApplication();

        $this->status = self::STATUS_ACCEPTED;
        $this->save();

        if ($staffComment) {
            $noteContent = "VT Application for " . $this->type_string . " " . $this->facility->name . " was accepted.\n" . $staffComment;
            $note = $this->account->addNote("visittransfer", $noteContent, $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ApplicationAccepted($this));
    }

    public function complete($staffComment = null, Account $actor = null)
    {
        $this->guardAgainstNonAcceptedApplication();

        $this->status = self::STATUS_COMPLETED;
//        $this->status = ($this->is_visit ? self::STATUS_COMPLETED : self::STATUS_PENDING_CERT);
        $this->save();

        if ($staffComment) {
            $noteContent = "VT Application for " . $this->type_string . " " . $this->facility->name . " was completed.\n" . $staffComment;
            $note = $this->account->addNote("visittransfer", $noteContent, $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ApplicationCompleted($this));
    }

    public function setCheckOutcome($check, $outcome)
    {
        $this->guardAgainstDuplicateCheckOutcomeSubmission($check);

        $columnName = "check_outcome_" . $check;

        $this->{$columnName} = (int)$outcome;
        $this->save();
    }

    public function settingToggle($setting)
    {
        switch ($setting) {
            case "training_required":
                return $this->settingToggleGenericBoolean("training_required");
            case "statement_required":
                $this->statement = null;

                return $this->settingToggleGenericBoolean("statement_required");
            case "references_required":
                return $this->settingToggleReferencesRequired();
            case "should_perform_checks":
                return $this->settingToggleGenericBoolean("should_perform_checks");
            case "will_auto_accept":
                return $this->settingToggleGenericBoolean("will_auto_accept");
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
        return false;
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
        $tableColumnName = "check_outcome_" . $check;
        if ($this->{$tableColumnName} !== null) {
            throw new CheckOutcomeAlreadySetException($this, $check);
        }
    }

    private function guardAgainstInvalidWithdrawal()
    {
        if($this->is_in_progress){
            return;
        }

        throw new ApplicationCannotBeWithdrawnException($this);
    }
}
