<?php

namespace App\Models\Mship\Feedback\Question;

use App\Models\Model;

/**
 * App\Models\Mship\Feedback\Question\Type.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $rules
 * @property int $max_uses
 * @property bool $requires_value
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Feedback\Question[] $questions
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question\Type findByName($name)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question\Type whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question\Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question\Type whereMaxUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question\Type whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question\Type whereRequiresValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Question\Type whereRules($value)
 *
 * @mixin \Eloquent
 */
class Type extends Model
{
    protected $table = 'mship_feedback_question_types';

    protected $fillable = [
        'name',
        'code',
        'max_uses',
        'requires_value',
    ];

    protected $casts = [
        'requires_value' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = false;

    public function scopeFindByName($query, $name)
    {
        return $query->where('name', $name)->firstOrFail();
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\Mship\Feedback\Question::class);
    }

    public function hasUnlimitedUses()
    {
        if ($this->max_uses == 0) {
            return true;
        }

        return false;
    }
}
