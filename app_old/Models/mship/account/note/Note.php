<?php

namespace App\Models\Mship\Account\Note;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Note extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;
        protected $table = "mship_account_note";
        protected $primaryKey = "account_note_id";
        protected $dates = ['created_at', 'deleted_at'];
        protected $hidden = ['account_note_id'];
        
        public function user(){
            return $this->belongsTo("\Models\Account\Account", "account_id", "account_id");
        }
        
        public function actioner(){
            return $this->belongsTo("\Models\Account\Account", "account_id", "actioner_id");
        }
        
        public function flag(){
            return $this->hasOne("\Models\Account\Note\Flag", "account_note_id", "account_note_id");
        }
        
        public function format(){
            return $this->hasOne("\Models\Account\Note\Format", "account_note_id", "account_note_id");
        }
        
        public function setDataAttribute($value){
            $this->attributes['data'] = serialize($value);
        }
        
}
