<?php

namespace App\Models\Mship\Feedback;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Mship\Feedback\Answer
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $question_id
 * @property string $response
 * @property-read \App\Models\Mship\Feedback\Feedback $feedback
 * @property-read \App\Models\Mship\Feedback\Question $question
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Answer notPermanent()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Answer whereFeedbackId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Answer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Answer whereQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Answer whereResponse($value)
 * @mixin \Eloquent
 */
class Answer extends Model
{
    protected $table = 'mship_feedback_answers';
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'feedback_id',
        'question_id',
        'response',
    ];

    public $timestamps = false;

    public function scopeNotPermanent($query)
    {
        return $query->whereHas('question', function ($q) {
            $q->where('permanent', false);
        });
    }

    public function feedback()
    {
        return $this->belongsTo(\App\Models\Mship\Feedback\Feedback::class);
    }

    public function question()
    {
        return $this->belongsTo(\App\Models\Mship\Feedback\Question::class);
    }
}
