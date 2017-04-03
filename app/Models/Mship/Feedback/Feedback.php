<?php

namespace App\Models\Mship\Feedback;

use Carbon\Carbon;
use App\Models\Mship\Feedback\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\Mship\Feedback\Question\Type;

class Feedback extends Model
{
    use Notifiable;

    protected $table        = 'mship_feedback';
    protected $dates        = [
        'created_at',
        'updated_at',
        'actioned_at',
    ];
    protected $fillable     = [
        'account_id',
        'submitter_account_id',
        'form_id',
    ];

    public function scopeATC($query)
    {
        // Find ATC form model
        $form = Form::where('slug','atc')->first();
        return $query->where('form_id', $form->id);
    }

    public function scopePilot($query)
    {
      // Find ATC form model
      $form = Form::where('slug','pilot')->first();
      return $query->where('form_id', $form->id);
    }

    public function form()
    {
        return $this->belongsTo('App\Models\Mship\Feedback\Form');
    }

    public function questions()
    {
        return Question::all();
    }

    public function answers()
    {
        return $this->hasMany('App\Models\Mship\Feedback\Answer');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Mship\Account');
    }

    public function submitter()
    {
        return $this->hasOne('App\Models\Mship\Account', 'id', 'submitter_account_id');
    }

    public function actioner()
    {
        return $this->hasOne('App\Models\Mship\Account', 'id', 'actioned_by_id');
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

    public function formSlug(){
        return $this->form->slug;
    }

    public function markActioned($actioner, $comment = null){
        $this->actioned_at = Carbon::now();
        $this->actioned_comment = $comment;
        $this->actioned_by_id = $actioner->id;
        $this->save();
    }

    public function markUnActioned(){
        $this->actioned_at = null;
        $this->actioned_comment = null;
        $this->actioned_by_id = null;
        $this->save();
    }

    public function getOptions($options){
      return json_decode($options);
    }
}
