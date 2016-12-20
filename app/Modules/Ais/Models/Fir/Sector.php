<?php

namespace App\Modules\Ais\Models\Fir;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $table      = 'ais_fir_sector';
    protected $primaryKey = 'id';
    public $timestamps    = true;
    public $dates         = ['created_at', 'updated_at', 'deleted_at'];
    public $fillable      = [
        'fir_id',
        'name',
        'name_radio',
        'callsign_primary',
        'callsign_secondary',
        'frequency',
    ];
}
