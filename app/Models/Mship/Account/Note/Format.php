<?php

namespace App\Models\Mship\Account\Note;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Format extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;
        protected $table = "mship_account_note_format";
        protected $primaryKey = "account_note_format_id";
        protected $dates = ['created_at', 'deleted_at'];
        protected $hidden = ['account_note_format_id'];
        
        public function note(){
            return $this->belongsTo("\Mship\Account\Note", "format_id", "account_note_format_id");
        }
}
