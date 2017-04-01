<?php

namespace App\Models\Mship\Feedback;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $table        = 'mship_feedback_questions';
    protected $dates        = [
        'created_at',
        'updated_at',
    ];
    protected $fillable     = [
        'type_id',
        'slug',
        'question',
        'options',
        'required',
    ];
    protected $casts = [
     'required'  => 'boolean',
     'options'   => 'array',
     'permanent' => 'boolean',
    ];

    public function answers()
    {
        return $this->hasMany('App\Models\Mship\Feedback\Answer');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\Mship\Feedback\Question\Type');
    }

}
