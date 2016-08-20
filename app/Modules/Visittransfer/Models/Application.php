<?php

namespace App\Modules\Visittransfer\Models;

use App\Models\Mship\Account;
use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use App\Modules\Visittransfer\Exceptions\Application\AttemptingToTransferToNonTrainingFacilityException;
use App\Modules\Visittransfer\Exceptions\Application\DuplicateRefereeException;
use App\Modules\Visittransfer\Exceptions\Application\FacilityHasNoCapacityException;
use App\Modules\Visittransfer\Exceptions\Application\TooManyRefereesException;
use App\Modules\Vt\Events\ApplicationCreated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Malahierba\PublicId\PublicId;

/**
 * App\Modules\Ais\Models\Fir
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Aerodrome[]  $airfields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Fir\Sector[] $sectors
 */
class Application extends Model
{
    use PublicId;

    static protected $public_id_salt = 'vatsim-uk-visiting-transfer-applications';
    static protected $public_id_min_length = 8;
    static protected $public_id_alphabet = 'upper_alphanumeric';

    protected $table    = "vt_application";
    protected $fillable = [
        "type",
        "account_id",
        "facility_id",
        "statement",
        "status",
    ];
    public $timestamps = true;
    protected $dates = [
        "submitted_at", "created_at", "updated_at"
    ];

    const TYPE_VISIT    = 10;
    const TYPE_TRANSFER = 40;

    const STATUS_IN_PROGRESS  = 10; // Member hasn't yet submitted application formally.
    const STATUS_SUBMITTED    = 30; // Member has formally submitted application.
    const STATUS_UNDER_REVIEW = 50; // References and checks have been completed.
    const STATUS_ACCEPTED     = 60; // Application has been accepted by staff
    const STATUS_COMPLETED    = 90; // Application has been formally completed, visit/transfer complete.
    const STATUS_LAPSED       = 97; // Application has lapsed.
    const STATUS_CANCELLED    = 98; // Application has been cancelled
    const STATUS_REJECTED     = 99; // Application has been rejected by staff

    static $APPLICATION_IS_CONSIDERED_OPEN = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_ACCEPTED,
    ];

    static $APPLICATION_IS_CONSIDERED_CLOSED = [
        self::STATUS_COMPLETED,
        self::STATUS_LAPSED,
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
        return $query->whereNotStatus($status);
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

    /** All Laravel relationships */
    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, "account_id", "id");
    }

    public function facility(){
        return $this->belongsTo(\App\Modules\Visittransfer\Models\Facility::class);
    }

    public function referees(){
        return $this->hasMany(\App\Modules\Visittransfer\Models\Reference::class);
    }

    /** All Laravel magic attributes **/
    public function setStatementAttribute($statement){
        $this->attributes['statement'] = trim($statement);
    }

    public function getPotentialFacilitiesAttribute(){
        if($this->facility){
            return collect([]);
        }

        if($this->is_visit){
            return Facility::all();
        }

        return Facility::trainingRequired()->get();
    }

    public function getIsOpenAttribute(){
        return $this->isStatusIn(self::$APPLICATION_IS_CONSIDERED_OPEN);
    }

    public function getRequiresActionAttribute(){
        return $this->isStatusIn(self::$APPLICATION_REQUIRES_ACTION);
    }

    public function getIsClosedAttribute(){
        return $this->isStatusIn(self::$APPLICATION_IS_CONSIDERED_CLOSED);
    }

    public function getIsInProgressAttribute(){
        return $this->isStatus(self::STATUS_IN_PROGRESS);
    }

    public function getIsSubmittedAttribute(){
        return $this->isStatus(self::STATUS_SUBMITTED);
    }

    public function getIsPendingReferencesAttribute(){
        return $this->referees->filter(function($ref){
            return !$ref->is_submitted;
        })->count() > 0;
    }

    public function getIsUnderReviewAttribute(){
        return $this->isStatus(self::STATUS_UNDER_REVIEW);
    }

    public function getIsAcceptedAttribute(){
        return $this->isStatus(self::STATUS_ACCEPTED);
    }

    public function getIsCompletedAttribute(){
        return $this->isStatus(self::STATUS_COMPLETED);
    }

    public function getIsLapsedAttribute(){
        return $this->isStatus(self::STATUS_LAPSED);
    }

    public function getIsRejectedAttribute(){
        return $this->isStatus(self::STATUS_REJECTED);
    }

    public function getStatusStringAttribute(){
        switch($this->attributes['status']){
            case self::STATUS_IN_PROGRESS:
                return "In Progress";
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

    public function getIsTrainingRequiredAttribute(){
        if(!$this->attributes['facility_id'] || !$this->facility){
            return true; // TODO: Logic check this.
        }

        return $this->facility->training_required == 1;
    }

    public function getIsStatementRequiredAttribute(){
        if(!$this->attributes['facility_id'] || !$this->facility){
            return true; // TODO: Logic check this.
        }

        return $this->facility->stage_statement_enabled == 1;
    }

    public function getIsRefernceRequiredAttribute(){
        if(!$this->attributes['facility_id'] || !$this->facility){
            return true; // TODO: Logic check this.
        }

        return $this->facility->stage_reference_enabled == 1;
    }

    public function getNumberReferencesRequiredAttribute(){
        if(!$this->attributes['facility_id'] || !$this->facility){
            return true; // TODO: Logic check this.
        }

        return $this->facility->stage_reference_quantity;
    }

    public function getNumberReferencesRequiredRelativeAttribute(){
        return $this->facility->stage_reference_quantity - $this->referees->count();
    }

    public function getAreChecksEnabledAttribute(){
        if(!$this->attributes['facility_id'] || !$this->facility){
            return true; // TODO: Logic check this.
        }

        return $this->facility->stage_checks_enabled == 1;
    }

    public function getWillBeAutoAcceptedAttribute(){
        if(!$this->attributes['facility_id'] || !$this->facility){
            return true; // TODO: Logic check this.
        }

        return $this->facility->auto_acceptance == 1;
    }

    public function getFacilityNameAttribute(){
        return $this->facility ? $this->facility->name : "Not selected";
    }

    /** Business logic. */
    public function isStatus($status){
        return $this->status == $status;
    }

    public function isStatusIn($stati){
        return in_array($this->attributes['status'], $stati);
    }

    public function setFacility(Facility $facility){
        $this->guardAgainstTransferringToANonTrainingFacility($facility);

        $this->guardAgainstApplyingToAFacilityWithNoCapacity($facility);

        $facility->applications()->save($this);
    }

    public function addReferee(Account $refereeAccount, $email, $relationship){
        $this->guardAgainstDuplicateReferee($refereeAccount);
        
        $this->guardAgainstTooManyReferees();

        $referee = new Reference([
            "email" => $email,
            "relationship" => $relationship,
        ]);

        $this->referees()->save($referee);

        $refereeAccount->visitTransferReferee()->save($referee);
    }

    public function setStatement($statement){
        $this->statement = $statement;
        $this->save();
    }

    public function submit(){
        $this->guardAgainstInvalidSubmission();

        $this->attributes['submitted_at'] = Carbon::now();
        $this->attributes['status'] = self::STATUS_SUBMITTED;
        $this->save();

        event(ApplicationSubmitted::class, $this);

        $this->facility->removeTrainingSpace();
    }

    /** Guards */
    private function guardAgainstTransferringToANonTrainingFacility(Facility $requestedFacility){
        if($this->is_transfer && $requestedFacility->training_required == 0){
            throw new AttemptingToTransferToNonTrainingFacilityException($requestedFacility);
        }
    }

    private function guardAgainstApplyingToAFacilityWithNoCapacity(Facility $requestedFacility){
        if($requestedFacility->training_required == 1 && $requestedFacility->training_spaces == 0){
            throw new FacilityHasNoCapacityException($requestedFacility);
        }
    }

    private function guardAgainstDuplicateReferee($refereeAccount){
        $checkContains = $this->referees->filter(function($referee) use($refereeAccount){
            return $referee->account_id == $refereeAccount->id;
        })->count() > 0;

        if($checkContains){
            throw new DuplicateRefereeException($refereeAccount);
        }
    }
    
    private function guardAgainstTooManyReferees(){
        if($this->number_references_required_relative == 0){
            throw new TooManyRefereesException($this);
        }
    }

    private function guardAgainstInvalidSubmission(){
        if($this->is_submitted){
            throw new ApplicationAlreadySubmittedException($this);
        }
    }


    // -- OLD -- //

    public function skippedStages()
    {
        return $this->hasMany(StageSkip::class, 'application_id', 'id');
    }

    public static function stageUnfinished($status)
    {
        return $status === self::STAGE_PENDING || $status === self::STAGE_INCOMPLETE;
    }

    public static function stageFinished($status)
    {
        return $status === self::STAGE_COMPLETE || $status === self::STAGE_SKIPPED;
    }

    public function stageSkipped($stage_key)
    {
        $this->skippedStages->load('stage');

        return !$this->skippedStages->filter(function ($skip) use ($stage_key) {
            return $skip->stage->key === $stage_key;
        })->isEmpty();
    }

    public function termsStatus()
    {
        return self::STAGE_COMPLETE;
    }

    public function typeStatus()
    {
        if ($this->type_id === null) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_COMPLETE;
        }
    }

    public function facilityStatus()
    {
        if ($this->stageSkipped('FACILITY')) {
            return self::STAGE_SKIPPED;
        } elseif ($this->stageUnfinished($this->typeStatus())) {
            return self::STAGE_PENDING;
        } elseif ($this->facility_id === null) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_COMPLETE;
        }
    }

    public function statementStatus()
    {
        if ($this->stageSkipped('STATEMENT')) {
            return self::STAGE_SKIPPED;
        } elseif ($this->stageUnfinished($this->facilityStatus())) {
            return self::STAGE_PENDING;
        } elseif (!$this->submitted_statement) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_COMPLETE;
        }
    }

    public function addRefereeStatus()
    {
        if ($this->stageSkipped('ADD_REFEREES')) {
            return self::STAGE_SKIPPED;
        } elseif ($this->stageUnfinished($this->statementStatus())) {
            return self::STAGE_PENDING;
        } elseif (!$this->submitted_referees) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_COMPLETE;
        }
    }

    public function submitStatus()
    {
        if ($this->submitted_application) {
            return self::STAGE_COMPLETE;
        } elseif ($this->stageFinished($this->addRefereeStatus())) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_PENDING;
        }
    }

    public function checksStatus()
    {
        //
    }

    public function refreviewStatus()
    {
        //
    }

    public function refsubmitStatus()
    {
        //
    }

    public function refdecisionStatus()
    {
        //
    }

    public function reviewStatus()
    {
        //
    }

    public function outcomeStatus()
    {
        //
    }

    public function getTypeStringAttribute(){
        if($this->is_visit){
            return "Visit";
        }

        return "Transfer";
    }

    public function getIsVisitAttribute(){
        return $this->type == self::TYPE_VISIT;
    }

    public function getIsTransferAttribute(){
        return $this->type == self::TYPE_TRANSFER;
    }
}