<?php

namespace App\Models\Mship\Feedback;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Mship\Feedback\Feedback
 *
 * @property int $id
 * @property int $form_id
 * @property int $account_id
 * @property int $submitter_account_id
 * @property \Carbon\Carbon $actioned_at
 * @property string $actioned_comment
 * @property int $actioned_by_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Mship\Account $actioner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Feedback\Answer[] $answers
 * @property-read \App\Models\Mship\Feedback\Form $form
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \App\Models\Mship\Account $submitter
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback aTC()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback pilot()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereActionedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereActionedById($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereActionedComment($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereFormId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereSubmitterAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Feedback\Feedback whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Feedback extends Model
{
    use Notifiable;

    protected $table = 'mship_feedback';
    protected $dates = [
        'created_at',
        'updated_at',
        'actioned_at',
    ];
    protected $fillable = [
        'account_id',
        'submitter_account_id',
        'form_id',
    ];

    public function scopeATC($query)
    {
        // Find ATC form model
        $form = Form::where('slug', 'atc')->first();

        return $query->where('form_id', $form->id);
    }

    public function scopePilot($query)
    {
        // Find ATC form model
      $form = Form::where('slug', 'pilot')->first();

        return $query->where('form_id', $form->id);
    }

    public function form()
    {
        return $this->belongsTo(\App\Models\Mship\Feedback\Form::class);
    }

    public function questions()
    {
        return Question::all();
    }

    public function answers()
    {
        return $this->hasMany(\App\Models\Mship\Feedback\Answer::class);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class);
    }

    public function submitter()
    {
        return $this->hasOne(\App\Models\Mship\Account::class, 'id', 'submitter_account_id');
    }

    public function actioner()
    {
        return $this->hasOne(\App\Models\Mship\Account::class, 'id', 'actioned_by_id');
    }

    public function isATC()
    {
        if ($this->formSlug() == 'atc') {
            return true;
        }

        return false;
    }

    public function isPilot()
    {
        if ($this->formSlug() == 'pilot') {
            return true;
        }

        return false;
    }

    public function formSlug()
    {
        return $this->form->slug;
    }

    public function markActioned($actioner, $comment = null)
    {
        $this->actioned_at = Carbon::now();
        $this->actioned_comment = $comment;
        $this->actioned_by_id = $actioner->id;
        $this->save();
    }

    public function markUnActioned()
    {
        $this->actioned_at = null;
        $this->actioned_comment = null;
        $this->actioned_by_id = null;
        $this->save();
    }

    public function getOptions($options)
    {
        return json_decode($options);
    }
}
