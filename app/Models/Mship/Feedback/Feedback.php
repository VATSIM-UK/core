<?php

namespace App\Models\Mship\Feedback;

use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Mship\Feedback\Feedback.
 *
 * @property int $id
 * @property int $form_id
 * @property int $account_id
 * @property int $submitter_account_id
 * @property \Carbon\Carbon|null $actioned_at
 * @property string|null $actioned_comment
 * @property int|null $actioned_by_id
 * @property \Carbon\Carbon|null $sent_at
 * @property string|null $sent_comment
 * @property int|null $sent_by_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property int|null $deleted_by
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Mship\Account $actioner
 * @property-read \App\Models\Mship\Account|null $deleter
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Feedback\Answer[] $answers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \App\Models\Mship\Feedback\Form $form
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \App\Models\Mship\Account $submitter
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback aTC()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback actioned()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback pilot()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback unActioned()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereActionedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereActionedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereActionedComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereSubmitterAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Feedback\Feedback whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Feedback extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $table = 'mship_feedback';

    protected $fillable = [
        'account_id',
        'submitter_account_id',
        'form_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'actioned_at' => 'datetime',
        'sent_at' => 'datetime',
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

    public function scopeActioned($query)
    {
        return $query->whereNotNull('actioned_at');
    }

    public function scopeUnActioned($query)
    {
        return $query->whereNull('actioned_at');
    }

    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
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

    public function position()
    {
        return $this->hasOne(\App\Models\Mship\Feedback\Answer::class)->whereHas('question', function ($query) {
            $query->where('slug', ['callsign3', 'sessionposition2']);
        });
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

    public function sender()
    {
        return $this->hasOne(\App\Models\Mship\Account::class, 'id', 'sent_by_id');
    }

    public function deleter()
    {
        return $this->hasOne(\App\Models\Mship\Account::class, 'id', 'deleted_by');
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

    public function markSent($sender, $comment = null)
    {
        $this->sent_at = Carbon::now();
        $this->sent_comment = $comment;
        $this->sent_by_id = $sender->id;
        $this->actioned_at = Carbon::now();
        $this->actioned_comment = 'Feedback automatically marked as actioned by sending feedback to member.';
        $this->actioned_by_id = $sender->id;
        $this->save();
    }

    public function markRejected($user = null)
    {
        if ($user) {
            $this->deleted_by = $user->id;
            $this->save();
        }
        $this->delete();
    }

    public function getOptions($options)
    {
        return json_decode($options);
    }

    public function getActionedAttribute()
    {
        return ! is_null($this->actioned_at);
    }
}
