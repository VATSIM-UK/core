<?php

namespace App\Models\Mship\Feedback;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mship\Feedback\Question;

class Feedback extends Model
{
    protected $table        = 'mship_feedback';
    protected $dates        = [
        'created_at',
        'updated_at',
    ];
    protected $fillable     = [
        'account_id',
        'submitter_account_id',
    ];

    public function questions()
    {
        return Question::all();
    }

    public function answers()
    {
        return $this->hasMany('App\Models\Mship\Feedback\Answer');;
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Mship\Account');
    }

    public function submitter()
    {
        return $this->hasOne('App\Models\Mship\Account', 'id', 'submitter_account_id');
    }
}
