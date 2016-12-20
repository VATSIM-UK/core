<?php

namespace App\Modules\Ais\Models\Facility;

use App\Modules\Ais\Models\Facility;
use App\Modules\NetworkData\Models\Atc;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table      = 'ais_facility_position';
    protected $primaryKey = 'id';
    public $timestamps    = true;
    public $dates         = ['created_at', 'updated_at', 'deleted_at'];
    public $fillable      = [
        'facility_id',
        'callsign_primary',
        'callsign_secondary',
        'callsign_format',
        'frequency',
        'logon_order',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'id');
    }

    public function airport()
    {
        return $this->facility->airport();
    }

    public function networkDataAtc()
    {
        return $this->hasMany(Atc::class, 'id', 'facility_position_id');
    }

    public function getNameAttribute()
    {
        if ($this->attributes['name'] !== null) {
            return $this->attributes['name'];
        }

        return $this->facility->name;
    }
}
