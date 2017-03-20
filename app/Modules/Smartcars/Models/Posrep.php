<?php

namespace App\Modules\Smartcars\Models;

use Illuminate\Database\Eloquent\Model;

class Posrep extends Model
{
    protected $table      = 'smartcars_posrep';
    protected $fillable   = [
        'bid_id',
        'flight_id',
    ];
    public $timestamps    = true;
    protected $dates      = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function bid()
    {
        return $this->hasOne(\App\Modules\Smartcars\Models\Flight::class, 'id', 'flight_id');
    }

    public function aircraft()
    {
        return $this->hasOne(\App\Modules\Smartcars\Models\Aircraft::class, 'id', 'aircraft_id');
    }
}
