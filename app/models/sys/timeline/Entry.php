<?php

namespace Models\Sys\Timeline;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Entry extends \Models\aModel {

	use SoftDeletingTrait;
        protected $table = "sys_timeline_entry";
        protected $primaryKey = "timeline_entry_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['timeline_entry_id'];
        protected $fillable = ['timeline_action_id', 'owner_id', 'owner_type', 'extra_id', 'extra_type', 'extra_data', 'ip'];

        public function owner(){
            return $this->morphTo()->withTrashed();
        }

        public function extra(){
            return $this->morphTo()->withTrashed();
        }

        public function getOwnerDisplayAttribute(){
            if($this->attributes['owner_id'] == NULL OR $this->attributes['owner_type'] == ""){
                return "Unknown";
            }
            if(is_object($this->owner) && isset($this->owner->display_value)){
                return $this->owner->display_value;
            } else {
                return "{timeline.entry.owner}";
            }
        }

        public function getExtraDisplayAttribute(){
            if($this->attributes['extra_id'] == NULL OR $this->attributes['extra_type'] == ""){
                return "Unknown";
            }
            if(is_object($this->extra) && isset($this->extra->display_value)){
                return $this->extra->display_value;
            } else {
                return "{timeline.entry.extra}";
            }
        }

        public function setExtraDataAttribute($value){
            $this->attributes['extra_data'] = json_encode($value);
        }

        public function getExtraDataAttribute(){
            return json_decode($this->attributes['extra_data']);
        }

        public function getEntryAttribute(){
            return $this->action->entry;
        }

        public function getEntryReplaceAttribute(){
            return $this->extra_display;
        }

        public function action(){
            return $this->belongsTo("Models\Sys\Timeline\Action", "timeline_action_id", "timeline_action_id");
        }

        public function setIpAttribute($value){
            $this->attributes['ip'] = ip2long($value);
        }

        public function getIpAttribute(){
            return long2ip($this->attributes['ip']);
        }

        public static function log($key, $owner, $extra=null, $data=array()){
            // Get the action
            $action = Action::where(\DB::raw("CONCAT(`type`, '_', `key`)"), "=", $key)->first();
            if(!$action){
                return false;
            }
            $action = $action->timeline_action_id;

            $entry = new Entry(array("timeline_action_id" => $action, "ip" => array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.255'), "extra_data" => $data));
            if(is_object($owner)){
                $owner->timelineEntriesOwner()->save($entry);
            } else {
                $entry->save();
            }
            if(is_object($extra)){
                $extra->timelineEntriesExtra()->save($entry);
            } else {
                $entry->extra_id = NULL;
                $entry->save();
            }
        }
}
