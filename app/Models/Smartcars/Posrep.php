<?php

namespace App\Models\Smartcars;

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
        return $this->hasOne(\App\Models\Smartcars\Flight::class, 'id', 'flight_id');
    }

    public function aircraft()
    {
        return $this->hasOne(\App\Models\Smartcars\Aircraft::class, 'id', 'aircraft_id');
    }
}
