<?php

namespace Models\Sys\Data;

class Change extends \Models\aModel {
        protected $table = "sys_data_change";
        protected $primaryKey = "data_change_id";
        protected $hidden = ['data_change_id'];

        public function model(){
            return $this->morphTo();
        }
}
