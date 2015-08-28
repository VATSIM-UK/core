<?php

namespace App\Models\Sys;

class Session extends \Models\aModel {
        protected $table = "sys_sessions";
        protected $primaryKey = "id";
        protected $hidden = ['session_id'];

        public function account(){
            return $this->belongsTo("\Models\Mship\Account", "session_id", "id");
        }
}
