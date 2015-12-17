<?php

namespace App\Models\Mship\Note;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Carbon\Carbon;

class Type extends \Eloquent {
	use SoftDeletingTrait;
        protected $table = "mship_note_type";
        protected $primaryKey = "note_type_id";
        protected $dates = ['created_at', 'deleted_at'];

        public static function scopeIsAvailable($query){
            return $query->whereIsAvailable(1);
        }

        public static function scopeIsSystem($query){
            return $query->whereIsSystem(1);
        }

        public static function scopeUsable($query){
            return $query->whereIsAvailable(1)->whereIsSystem(0);
        }

        public function notes(){
            return $this->hasMany("\App\Models\Mship\Account\Note", "note_type_id", "note_type_id");
        }
}
