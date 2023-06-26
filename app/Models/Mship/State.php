<?php

namespace App\Models\Mship;

/**
 * App\Models\Mship\State.
 *
 * @property int $id
 * @property string $code
 * @property string $type
 * @property string $name
 * @property string $division
 * @property string $region
 * @property int $delete_all_temps
 * @property int $priority
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $account
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read mixed $is_division
 * @property-read mixed $is_permanent
 * @property-read mixed $is_temporary
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State hasCode($code)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State ofType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State permanent()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State temporary()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereDeleteAllTemps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereDivision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\State whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class State extends \App\Models\Model
{
    protected $table = 'mship_state';

    protected $primaryKey = 'id';

    protected $dates = ['created_at', 'deleted_at'];

    protected $hidden = ['id'];

    public static function findByCode($code)
    {
        return self::hasCode($code)->first();
    }

    public function scopeOfType($query, $type)
    {
        return $query->whereType($type);
    }

    public function scopePermanent($query)
    {
        return $query->ofType('perm');
    }

    public function scopeTemporary($query)
    {
        return $query->ofType('temp');
    }

    public function scopeHasCode($query, $code)
    {
        return $query->whereCode($code);
    }

    public function account()
    {
        return $this->belongsToMany(Account::class, 'mship_account_state', 'state_id', 'account_id')
            ->withPivot(['region', 'division', 'start_at', 'end_at']);
    }

    public function setDivisionAttribute(array $division)
    {
        $this->attributes['division'] = json_encode($division);
    }

    public function getDivisionAttribute()
    {
        return collect(json_decode($this->attributes['division']));
    }

    public function setRegionAttribute(array $division)
    {
        $this->attributes['region'] = json_encode($division);
    }

    public function getRegionAttribute()
    {
        return collect(json_decode($this->attributes['region']));
    }

    public function getIsPermanentAttribute()
    {
        return $this->type == 'perm';
    }

    public function getIsTemporaryAttribute()
    {
        return $this->type == 'temp';
    }

    public function getIsDivisionAttribute()
    {
        return $this->name == 'Division';
    }

    public function __toString()
    {
        return '['.$this->code.'] '.$this->name;
    }
}
