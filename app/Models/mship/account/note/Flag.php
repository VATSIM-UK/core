<?php

namespace Models\Mship\Account\Note;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Flag extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;
        protected $table = "mship_account_note_flag";
        protected $primaryKey = "account_note_flag_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['account_note_flag_id'];
        
        public function flagger(){
            return $this->belongsTo("\Mship\Account", "account_id", "flag_by");
        }
        
        public function resolver(){
            return $this->belongsTo("\Mship\Account", "account_id", "resolve_by");
        }
        
        public function note(){
            return $this->belongsTo("\Mship\Note", "flag_id", "account_note_flag_id");
        }
}
