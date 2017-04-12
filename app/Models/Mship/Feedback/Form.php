<?php

namespace App\Models\Mship\Feedback;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Mship\Feedback\Form
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Feedback\Question[] $questions
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Form extends Model
{
    use SoftDeletes;

    protected $table = 'mship_feedback_forms';
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $fillable = [
        'name',
        'slug',
    ];

    public function questions()
    {
        return $this->hasMany(\App\Models\Mship\Feedback\Question::class);
    }
}
