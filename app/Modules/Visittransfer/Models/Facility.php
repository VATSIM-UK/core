<?php

namespace App\Modules\Visittransfer\Models;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Modules\Visittransfer\Models\Facility\Email;
use App\Modules\Visittransfer\Exceptions\Facility\DuplicateFacilityNameException;

/**
 * App\Modules\Visittransfer\Models\Facility
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $can_transfer
 * @property bool $can_visit
 * @property bool $training_required
 * @property string $training_team
 * @property int $training_spaces
 * @property bool $stage_statement_enabled
 * @property bool $stage_reference_enabled
 * @property int $stage_reference_quantity
 * @property bool $stage_checks
 * @property bool $auto_acceptance
 * @property bool $open
 * @property string $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Visittransfer\Models\Application[] $applications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Visittransfer\Models\Facility\Email[] $emails
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility atc()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility canTransfer()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility canVisit()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility hasTrainingSpace()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility isClosed()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility isOpen()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility onlyTransfer()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility onlyVisit()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility pilot()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility trainingRequired()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereAutoAcceptance($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereCanTransfer($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereCanVisit($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereOpen($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereStageChecks($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereStageReferenceEnabled($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereStageReferenceQuantity($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereStageStatementEnabled($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereTrainingRequired($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereTrainingSpaces($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility whereTrainingTeam($value)
 * @mixin \Eloquent
 */
class Facility extends Model
{
    use Notifiable;

    protected $table = 'vt_facility';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $fillable = [
        'name',
        'description',
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

        $input_emails = array_filter($attributes['acceptance_emails']);
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
            if (!$current_emails->contains('email', $email)) {
                $new_email = new Email(['email' => $email]);
                $this->emails()->save($new_email);
            }
        }

        foreach ($current_emails as $email) {
            if (array_search($email->email, $input_emails) === false) {
                $email->delete();
            }
        }

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
        return $query;
    }

    public function scopeIsClosed($query)
    {
        return $query;
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
        return $this->hasMany(\App\Modules\Visittransfer\Models\Application::class);
    }

    public function emails()
    {
        return $this->hasMany(\App\Modules\Visittransfer\Models\Facility\Email::class);
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

        if (!$excludeCurrent && self::where('name', 'LIKE', $proposedName)->count() > 0) {
            throw new DuplicateFacilityNameException($proposedName);
        }
    }
}
