<?php

namespace App\Models\Mship\Feedback;

use App\Models\Contact;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Mship\Feedback\Form.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $contact_id
 * @property int $enabled
 * @property int $targeted
 * @property int $public
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Contact|null $contact
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Feedback\Feedback[] $feedback
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Feedback\Question[] $questions
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form public()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereTargeted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Form whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Form withoutTrashed()
 *
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

    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\Mship\Feedback\Question::class);
    }

    public function feedback()
    {
        return $this->hasMany(\App\Models\Mship\Feedback\Feedback::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
