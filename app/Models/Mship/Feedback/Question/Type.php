<?php

namespace App\Models\Mship\Feedback\Question;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table        = 'mship_feedback_question_types';
    protected $dates        = [
        'created_at',
        'updated_at',
    ];
    protected $fillable     = [
        'name',
        'code',
        'max_uses',
        'requires_value',
    ];
    protected $casts = [
     'requires_value' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany('App\Models\Mship\Feedback\Question');
    }

    public function canBeUsedAgain(){
        if($this->hasUnlimitedUses()){
          return true;
        }

        if($this->questions->count() == $this->max_uses){
          return false;
        }
    }

    public function hasUnlimitedUses()
    {
        if ($this->max_uses == 0) {
            return true;
        }

        return false;
    }
}
