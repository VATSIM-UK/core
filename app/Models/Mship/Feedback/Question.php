<?php

namespace App\Models\Mship\Feedback;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Mship\Feedback\Question.
 *
 * @property int $id
 * @property int $type_id
 * @property int $form_id
 * @property string $slug
 * @property string $question
 * @property array $options
 * @property bool $required
 * @property int $sequence
 * @property bool $permanent
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Feedback\Answer[] $answers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \App\Models\Mship\Feedback\Form $form
 * @property-read \App\Models\Mship\Feedback\Question\Type $type
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question notActioned()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question notPermanent()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Question onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question wherePermanent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Question withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Question withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Question extends Model
{
    use SoftDeletes;

    protected $table = 'mship_feedback_questions';

    protected $fillable = [
        'type_id',
        'slug',
        'question',
        'options',
        'required',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'permanent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeNotPermanent($query)
    {
        return $query->where('permanent', false);
    }

    public function scopeNotActioned($query)
    {
        return $query->where('actioned_at', null);
    }

    public function form()
    {
        return $this->belongsTo(\App\Models\Mship\Feedback\Form::class);
    }

    public function answers()
    {
        return $this->hasMany(\App\Models\Mship\Feedback\Answer::class);
    }

    public function type()
    {
        return $this->belongsTo(\App\Models\Mship\Feedback\Question\Type::class);
    }

    public function optionValues()
    {
        if (isset($this->options['values'])) {
            return $this->options['values'];
        }

        return false;
    }
}
