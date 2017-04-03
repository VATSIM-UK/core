<?php

namespace App\Models\Mship\Feedback;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $table        = 'mship_feedback_forms';
    protected $dates        = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $fillable     = [
        'name',
        'slug',
    ];

    public function questions()
    {
        return $this->hasMany('App\Models\Mship\Feedback\Question');
    }

}
