<?php

namespace App\Models\VisitTransfer;

use App\Exceptions\VisitTransfer\Facility\DuplicateFacilityNameException;
use App\Models\Contact;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Malahierba\PublicId\PublicId;

/**
 * App\Models\VisitTransfer\Facility.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $can_transfer
 * @property int $can_visit
 * @property int $training_required
 * @property string $training_team
 * @property int|null $training_spaces
 * @property int $stage_statement_enabled
 * @property int $stage_reference_enabled
 * @property int $stage_reference_quantity
 * @property int $stage_checks
 * @property int $auto_acceptance
 * @property int $open
 * @property int $public
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VisitTransfer\Application[] $applications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VisitTransfer\Facility\Email[] $emails
 * @property-read string $public_id
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility atc()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility canTransfer()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility canVisit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility hasTrainingSpace()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility isClosed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility isOpen()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility onlyTransfer()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility onlyVisit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility pilot()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility public ()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility trainingRequired()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereAutoAcceptance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereCanTransfer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereCanVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereStageChecks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereStageReferenceEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereStageReferenceQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereStageStatementEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereTrainingRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereTrainingSpaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility whereTrainingTeam($value)
 *
 * @mixin \Eloquent
 */
class Facility extends Model
{
    use HasFactory, Notifiable, PublicId;

    protected static $public_id_salt = 'vatsim-uk-visiting-transfer-facility';

    protected static $public_id_min_length = 8;

    protected static $public_id_alphabet = 'upper_alphanumeric';

    protected $table = 'vt_facility';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public $casts = [
        'can_visit' => 'boolean',
        'can_transfer' => 'boolean',
        'training_required' => 'boolean',
        'stage_statement_enabled' => 'boolean',
        'stage_reference_enabled' => 'boolean',
        'stage_checks' => 'boolean',
        'auto_acceptance' => 'boolean',
        'open' => 'boolean',
        'public' => 'boolean',
    ];

    public $fillable = [
        'name',
        'description',
        'open',
        'can_visit',
        'can_transfer',
        'training_required',
        'training_team',
        'training_spaces',
        'stage_statement_enabled',
        'stage_reference_enabled',
        'stage_reference_quantity',
        'stage_checks',
        'auto_acceptance',
        'public',
    ];

    public function routeNotificationForMail()
    {
        if ($this->emails->count() === 0) {
            $contactKey = sprintf('%s_TRAINING', strtoupper($this->training_team));

            return Contact::where('key', $contactKey)->first()->email;
        } else {
            return $this->emails->pluck('email');
        }
    }

    public static function isPossibleToVisitAtc()
    {
        return self::canVisit()->atc()->isOpen()->count() > 0;
    }

    public static function isPossibleToVisitPilot()
    {
        return self::canVisit()->pilot()->isOpen()->count() > 0;
    }

    public static function isPossibleToTransfer()
    {
        return self::canTransfer()->isOpen()->hasTrainingSpace()->count() > 0;
    }

    public static function create(array $attributes = [])
    {
        (new self)->guardAgainstDuplicateFacilityName(array_get($attributes, 'name', ''));

        return static::query()->create($attributes);
    }

    public function update(array $attributes = [], array $options = [])
    {
        (new self)->guardAgainstDuplicateFacilityName(array_get($attributes, 'name', ''), $this->id);

        if (strcasecmp(array_get($attributes, 'training_spaces', null), 'null') == 0) {
            $attributes['training_spaces'] = null;
        }

        /*$input_emails = array_filter($attributes['acceptance_emails']);
        shuffle($input_emails);
        $current_emails = $this->emails()->get();

        // We don't want these used down the line
        unset($attributes['acceptance_emails']);

        if (count($input_emails) == 0 && $current_emails->count() > 0) {
            foreach ($current_emails as $email) {
                $email->delete();
            }

            return parent::update($attributes, $options);
        }

        foreach ($input_emails as $key => $email) {
            if (! $current_emails->contains('email', $email)) {
                $new_email = new Facility\Email(['email' => $email]);
                $this->emails()->save($new_email);
            }
        }

        foreach ($current_emails as $email) {
            if (array_search($email->email, $input_emails) === false) {
                $email->delete();
            }
        }*/

        return parent::update($attributes, $options);
    }

    public function scopeAtc($query)
    {
        return $query->where('training_team', '=', 'atc');
    }

    public function scopePilot($query)
    {
        return $query->where('training_team', '=', 'pilot');
    }

    public function scopeIsOpen($query)
    {
        return $query->where('open', true);
    }

    public function scopeIsClosed($query)
    {
        return $query->where('open', false);
    }

    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    public function scopeCanVisit($query)
    {
        return $query->where('can_visit', '=', '1');
    }

    public function scopeOnlyVisit($query)
    {
        return $query->where('can_visit', '=', '1')->where('can_transfer', '=', '0');
    }

    public function scopeCanTransfer($query)
    {
        return $query->where('can_transfer', '=', '1')->trainingRequired();
    }

    public function scopeOnlyTransfer($query)
    {
        return $query->where('can_visit', '=', '0')->where('can_transfer', '=', '1');
    }

    public function scopeTrainingRequired($query)
    {
        return $query->where('training_required', '=', 1);
    }

    public function scopeHasTrainingSpace($query)
    {
        return $query->where(function ($query) {
            $query->where('training_spaces', '>', 0)
                ->orWhereNull('training_spaces');
        });
    }

    public function applications()
    {
        return $this->hasMany(\App\Models\VisitTransfer\Application::class);
    }

    public function emails()
    {
        return $this->hasMany(Facility\Email::class);
    }

    public function addTrainingSpace()
    {
        if ($this->training_required == 1 && $this->training_spaces !== null) {
            $this->increment('training_spaces');
        }
    }

    public function removeTrainingSpace()
    {
        if ($this->training_required == 1 && $this->training_spaces !== null) {
            $this->decrement('training_spaces');
        }
    }

    private function guardAgainstDuplicateFacilityName($proposedName, $excludeCurrent = false)
    {
        if ($excludeCurrent && self::where('id', '!=', $excludeCurrent)
            ->where('name', 'LIKE', $proposedName)
            ->count() > 0
        ) {
            throw new DuplicateFacilityNameException($proposedName);
        }

        if (! $excludeCurrent && self::where('name', 'LIKE', $proposedName)->count() > 0) {
            throw new DuplicateFacilityNameException($proposedName);
        }
    }
}
