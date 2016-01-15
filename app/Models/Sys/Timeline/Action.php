<?php

namespace App\Models\Sys\Timeline;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sys\Timeline\Action
 *
 * @property integer $timeline_action_id
 * @property string $section
 * @property string $area
 * @property string $action
 * @property integer $version
 * @property string $entry
 * @property boolean $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $entries
 * @property-read mixed $type
 */
class Action extends \App\Models\aModel {

	use SoftDeletingTrait;
        protected $table = "sys_timeline_action";
        protected $primaryKey = "timeline_action_id";
        protected $dates = ['created_at', 'deleted_at'];

        public function entries(){
            return $this->hasMany("App\Models\Sys\Timeline\Entry", "timeline_action_id", "timeline_action_id");
        }

        public function getTypeAttribute(){
            return array_get($this->attributes, "section").".".array_get($this->attributes, "area");
        }
}
