<?php

namespace App\Modules\Visittransfer\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Visittransfer\Models\Facility\Email;
use App\Modules\Visittransfer\Exceptions\Facility\DuplicateFacilityNameException;

class Facility extends Model
{
    protected $table      = 'vt_facility';
    protected $primaryKey = 'id';
    public $timestamps    = false;
    public $fillable      = [
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

        return parent::create($attributes);
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

        foreach ($input_emails as $key=>$email) {
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
