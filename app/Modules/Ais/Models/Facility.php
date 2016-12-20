<?php

namespace App\Modules\Ais\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $table      = 'ais_facility';
    protected $primaryKey = 'id';
    public    $timestamps = true;
    public    $dates      = ['created_at', 'updated_at', 'deleted_at'];
    public    $fillable   = [
        "name",
    ];
}
