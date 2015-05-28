<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use \Validator;

class Email extends \Eloquent {

    use SoftDeletingTrait;

    protected $table = "mship_account_email";
    protected $primaryKey = "account_email_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['account_email_id'];
    protected $fillable = ['email'];
    protected $attributes = ['is_primary' => 0];

    public function account() {
        return $this->hasOne("\Models\Mship\Account", "account_id", "account_id");
    }

    public function tokens() {
        return $this->morphMany("\Models\Sys\Token", "related");
    }

    public function scopePrimary($query){
        return $query->where("is_primary", "=", 1);
    }

    public function scopeSecondary($query){
        return $query->where("is_primary", "=", 0);
    }

    public function scopeVerified($query){
        return $query->where("verified_at", ">", "0000-00-00 00:00:00");
    }

    public function ssoEmails() {
        return $this->hasMany("\Models\Sso\Email", "account_email_id", "account_email_id");
    }

    public function setEmailAttribute($value) {
        $value = trim($value);
        $value = strtolower($value);
        $this->attributes['email'] = $value;
    }

    public function getIsVerifiedAttribute(){
        return $this->attributes['verified_at'] != NULL;
    }

    public function getIsPrimaryAttribute(){
        return (boolean) $this->attributes['is_primary'];
    }

    public function setIsPrimaryAttribute($value){
        // Are we just setting to false?
        if($value == 0 OR !$value){
            $this->attributes['is_primary'] = 0;
            $this->save();
            return true;
        }

        // Next, let's check if this email is already primary.  If it is, no chance.
        if($this->is_primary){
            return false;
        }

        // Finally, let's demote other primary emails.
        if($this->account){
            foreach($this->account->emails as $e){
                $e->is_primary = 0;
                $e->save();
            }
        }

        // Now upgrade this!
        $this->attributes['is_primary'] = 1;
        $this->save();
    }

    public function __toString(){
        return isset($this->attributes['email']) ? $this->attributes['email'] : "";
    }
}
