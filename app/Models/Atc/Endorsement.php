<?php

namespace App\Models\Atc;

use Illuminate\Database\Eloquent\Model;

class Endorsement extends Model
{
    protected $table = 'endorsements';
    protected $fillable = [
        'endorsement',
        'required_airfields',
        'required_hours',
        'hours_months',
    ];
    public $timestamps = true;

}
