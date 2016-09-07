<?php namespace App\Modules\Visittransfer\Models;

use App\Models\Mship\Account;
use App\Models\Mship\Note\Type;
use App\Models\Sys\Token;
use App\Modules\Visittransfer\Events\ReferenceAccepted;
use App\Modules\Visittransfer\Events\ReferenceDeleted;
use App\Modules\Visittransfer\Events\ReferenceRejected;
use App\Modules\Visittransfer\Events\ReferenceUnderReview;
use App\Modules\Visittransfer\Exceptions\Reference\ReferenceAlreadySubmittedException;
use App\Modules\Visittransfer\Exceptions\Reference\ReferenceNotUnderReviewException;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Visittransfer\Models\Reference
 *
 */
class Reference extends Model
{

    protected $table      = "vt_reference";
    protected $primaryKey = "id";
    protected $fillable   = [
        "application_id",
        "account_id",
        "email",
        "relationship",
        "status",
        "status_note",
    ];
    protected $touches    = ["application"];
    public $timestamps = false;

    const STATUS_DRAFT        = 10;
    const STATUS_REQUESTED    = 30;
    const STATUS_UNDER_REVIEW = 50;
    const STATUS_ACCEPTED     = 90;
    const STATUS_REJECTED     = 95;

    static public $REFERENCE_IS_SUBMITTED = [
        self::STATUS_UNDER_REVIEW,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
    ];

    public static function scopePending($query)
    {
        return $query->whereNull("submitted_at");
    }

    public static function scopeStatus($query, $status)
    {
        return $query->where("status", "=", $status);
    }

    public static function scopeStatusIn($query, array $stati)
    {
        return $query->whereIn("status", $stati);
    }

    public static function scopeDraft($query)
    {
        return $query->status(self::STATUS_DRAFT);
    }

    public static function scopeRequested($query)
    {
        return $query->status(self::STATUS_REQUESTED);
    }

    public static function scopeSubmitted($query)
    {
        return $query->statusIn(self::$REFERENCE_IS_SUBMITTED);
    }

    public static function scopeUnderReview($query)
    {
        return $query->status(self::STATUS_UNDER_REVIEW);
    }

    public static function scopeAccepted($query)
    {
        return $query->status(self::STATUS_ACCEPTED);
    }

    public static function scopeRejected($query)
    {
        return $query->status(self::STATUS_REJECTED);
    }

    public function delete()
    {
        event(new ReferenceDeleted($this));

        parent::delete();
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class);
    }

    public function application()
    {
        return $this->belongsTo(\App\Modules\Visittransfer\Models\Application::class);
    }

    public function tokens()
    {
        return $this->morphOne(Token::class, 'related');
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Mship\Account\Note::class, "attachment");
    }

    public function getTokenAttribute()
    {
        return $this->tokens;
    }

    public function getIsSubmittedAttribute()
    {
        return in_array($this->state, self::$REFERENCE_IS_SUBMITTED);
    }

    public function getIsRequestedAttribute()
    {
        return $this->status == self::STATUS_REQUESTED;
    }

    public function getIsUnderReviewAttribute()
    {
        return $this->status == self::STATUS_UNDER_REVIEW;
    }

    public function getIsAcceptedAttribute()
    {
        return $this->status == self::STATUS_ACCEPTED;
    }

    public function getIsRejectedAttribute()
    {
        return $this->status == self::STATUS_REJECTED;
    }

    public function getStatusStringAttribute()
    {
        switch ($this->attributes['status']) {
            case self::STATUS_DRAFT:
                return "Draft";
            case self::STATUS_REQUESTED:
                return "Requested";
            case self::STATUS_UNDER_REVIEW:
                return "Under Review";
            case self::STATUS_ACCEPTED:
                return "Accepted";
            case self::STATUS_REJECTED:
                return "Rejected";
        }
    }

    public function generateToken()
    {
        $expiryTimeInMinutes = 1440 * 14; // 14 days

        return Token::generate("visittransfer_reference_request", false, $this, $expiryTimeInMinutes);
    }

    public function submit($referenceContent)
    {
        $this->guardAgainstReSubmittingReference();

        $this->reference = $referenceContent;
        $this->status = self::STATUS_UNDER_REVIEW;
        $this->submitted_at = \Carbon\Carbon::now();
        $this->save();

        event(new ReferenceUnderReview($this));
    }

    public function reject($publicReason = "No reason was provided.", $staffReason = null, Account $actor = null)
    {
        $this->guardAgainstNonUnderReviewReference();

        $this->status = self::STATUS_REJECTED;
        $this->status_note = $publicReason;
        $this->save();

        if ($staffReason) {
            $noteContent = "VT Reference from " . $this->account->name . " was rejected.\n" . $staffReason;
            $note = $this->application->account->addNote(Type::isShortCode('visittransfer')->first(), $noteContent,
                $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ReferenceRejected($this));
    }

    public function accept($staffComment = null, Account $actor = null)
    {
        $this->guardAgainstNonUnderReviewReference();

        $this->status = self::STATUS_ACCEPTED;
        $this->save();

        if ($staffComment) {
            $noteContent = "VT Reference from " . $this->account->name . " was accepted.\n" . $staffComment;
            $note = $this->application->account->addNote("visittransfer", $noteContent, $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ReferenceAccepted($this));
    }

    private function guardAgainstReSubmittingReference()
    {
        if (!$this->is_requested) {
            throw new ReferenceAlreadySubmittedException($this);
        }
    }

    private function guardAgainstNonUnderReviewReference()
    {
        if ($this->status != self::STATUS_UNDER_REVIEW) {
            throw new ReferenceNotUnderReviewException($this);
        }
    }
}
