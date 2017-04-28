<?php

namespace App\Models\Mship;

use App\Traits\RecordsActivity;

class State extends \Eloquent
{
    use RecordsActivity;
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

    public function __toString()
    {
        return '['.$this->code.'] '.$this->name;
    }
}
