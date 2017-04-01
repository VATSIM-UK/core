<?php

namespace App\Models\Mship\Feedback;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
  protected $table        = 'mship_feedback_answers';
  protected $dates        = [
      'created_at',
      'updated_at',
  ];
  protected $fillable     = [
      'feedback_id',
      'question_id',
      'response',
  ];

  public $timestamps = false;

  public function feedback()
  {
      return $this->belongsTo('App\Models\Mship\Feedback\Feedback');
  }

  public function question()
  {
      return $this->belongsTo('App\Models\Mship\Feedback\Question');
  }

}
