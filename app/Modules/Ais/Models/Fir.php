<?php

namespace App\Modules\Ais\Models;

use Illuminate\Database\Eloquent\Model;

class Fir extends Model
{
    protected $table      = 'ais_fir';
    protected $primaryKey = 'id';
    public $timestamps    = true;
    public $dates         = ['created_at', 'updated_at', 'deleted_at'];
    public $fillable      = [
        'icao',
        'name',
    ];
}
