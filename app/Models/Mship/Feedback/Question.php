<?php

namespace App\Models\Mship\Feedback;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
  protected $table        = 'mship_feedback_questions';
  protected $dates        = [
      'created_at',
      'updated_at',
  ];
  protected $fillable     = [
      'slug',
      'question',
      'type',
      'options',
      'required',
  ];
  protected $casts = [
   'required' => 'boolean',
   'options' => 'array'
];

  public function answers()
  {
      return $this->hasMany('App\Models\Mship\Feedback\Answer');
  }

}
