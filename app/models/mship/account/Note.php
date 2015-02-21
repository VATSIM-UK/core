<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Note extends \Models\aModel {

	use SoftDeletingTrait;
        protected $table = "mship_account_note";
        protected $primaryKey = "account_note_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];

        public function account(){
            return $this->belongsTo("\Models\Mship\Account", "account_id", "account_id");
        }

        public function writer(){
            return $this->belongsTo("\Models\Mship\Account", "account_id", "writer_id");
        }

        public function type(){
            return $this->belongsTo("\Models\Mship\Note\Type", "note_type_id", "note_type_id");
        }
}
