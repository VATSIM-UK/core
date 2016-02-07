<?php

namespace App\Models\Sys;

/**
 * App\Models\Sys\Session
 *
 * @property string $id
 * @property string $payload
 * @property integer $last_activity
 * @property-read \App\Models\Mship\Account $account
 */
class Session extends \App\Models\aModel {
        protected $table = "sys_sessions";
        protected $primaryKey = "id";
        protected $hidden = ['session_id'];

        public function account(){
            return $this->belongsTo("\App\Models\Mship\Account", "session_id", "id");
        }
}
