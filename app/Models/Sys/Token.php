<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Token extends \App\Models\aTimelineEntry {

    use SoftDeletingTrait;

    protected $table = "sys_token";
    protected $primaryKey = "token_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['token_id'];

    public function related() {
        return $this->morphTo();
    }

    public function scopeOfType($query, $type){
        return $query->where("type", "=", $type);
    }

    public function scopeExpired($query){
        return $query->where("expires_at", "<=", \Carbon\Carbon::now()->toDateTimeString());
    }

    public function scopeNotExpired($query){
        return $query->where("expires_at", ">=", \Carbon\Carbon::now()->toDateTimeString());
    }

    public function scopeUsed($query){
        return $query->whereNotNull("used_at");
    }

    public function scopeNotUsed($query){
        return $query->whereNull("used_at");
    }

    public function scopeValid($query){
        return $query->notUsed()->notExpired();
    }

    public static function generate($type, $allowDuplicates = false, $relation = null) {
        if ($allowDuplicates == false) {
            foreach ($relation->tokens()->whereType($type)->notExpired()->get() as $t) {
                $t->delete();
            }
        }

        $token = new Token;
        $token->type = $type;
        $token->expires_at = \Carbon\Carbon::now()->addDay()->toDateTimeString();
        $token->code = uniqid(uniqid());

        if ($relation != null) {
            $relation->tokens()->save($token);
        } else {
            $token->save();
        }

        return $token;
    }

    public function consume(){
        if(!$this OR $this->is_used OR $this->is_expired){
            return false;
        }

        $this->used_at = \Carbon\Carbon::now()->toDateTimeString();
        $this->save();
    }

    public function getIsUsedAttribute() {
        return ($this->attributes['used_at'] != NULL && \Carbon\Carbon::parse($this->attributes['used_at'])->isPast());
    }

    public function getIsExpiredAttribute(){
        return \Carbon\Carbon::parse($this->attributes['expires_at'])->isPast();
    }

    public function __toString(){
        return array_get($this->attributes, "code", "NoValue");
    }

    public function getDisplayValueAttribute() {

    }

}
