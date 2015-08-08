<?php

namespace Models\Sys\Timeline;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Action extends \Models\aModel {

	use SoftDeletingTrait;
        protected $table = "sys_timeline_action";
        protected $primaryKey = "timeline_action_id";
        protected $dates = ['created_at', 'deleted_at'];

        public function entries(){
            return $this->hasMany("Models\Sys\Timeline\Entry", "timeline_action_id", "timeline_action_id");
        }

        public function getTypeAttribute(){
            return array_get($this->attributes, "section").".".array_get($this->attributes, "area");
        }
}
