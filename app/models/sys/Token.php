<?php

namespace Models\Sys;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Token extends \Models\aTimelineEntry {

	use SoftDeletingTrait;
        protected $table = "sys_token";
        protected $primaryKey = "token_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['token_id'];

        public function related(){
            return $this->morphTo();
        }

        public static function generate($type, $allowDuplicates=false, $relation=null){
            if($allowDuplicates == false){
                foreach($relation->tokens()->where("type", "=", $type)->where("expires_at", ">=", \Carbon\Carbon::now()->toDateTimeString())->get() as $t){
                    $t->delete();
                }
            }

            $token = new Token;
            $token->type = $type;
            $token->expires_at = \Carbon\Carbon::now()->addDay()->toDateTimeString();
            $token->code = uniqid(uniqid());

            if($relation != null){
                $relation->tokens()->save($token);
            } else {
                $token->save();
            }

            return $token;
        }

    public function getDisplayValueAttribute() {

    }

    public function getIsUsedAttribute(){
        return ($this->attributes['used_at'] != NULL && \Carbon\Carbon::parse($this->attributes['used_at'])->isPast());
    }

}
