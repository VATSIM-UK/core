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
        return $this->belongsTo("\Models\Mship\Account\Account", "account_id", "account_id");
    }

    public function scopePrimary($query){
        return $query->where("is_primary", "=", 1);
    }

    public function scopeSecondary($query){
        return $query->where("is_primary", "=", 0);
    }

    public function scopeVerified($query){
        return $query->where("verified", ">", "0000-00-00 00:00:00");
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
        return $this->attributes['verified'] != NULL;
    }

    public function getIsPrimaryAttribute(){
        return (boolean) $this->attributes['is_primary'];
    }

    public function setIsPrimaryAttribute($value){
        // First off, if this isn't a real email. Sod off.
        if(!$this OR !$this->account){
            \Log::info(__FILE__.":".__LINE__);
            return false;
        }

        // Are we just setting to false?
        if($value == 0 OR !$value){
            $this->attributes['is_primary'] = 0;
            $this->save();
            \Log::info(__FILE__.":".__LINE__);
            return true;
        }

        // Next, let's check if this email is already primary.  If it is, no chance.
        if($this->is_primary){
            \Log::info(__FILE__.":".__LINE__);
            return false;
        }

        // Finally, let's demote other primary emails.
        foreach($this->account->emails as $e){
            $e->is_primary = 0;
            $e->save();
            \Log::info(__FILE__.":".__LINE__);
        }

        // Now upgrade this!
        $this->attributes['is_primary'] = 1;
        $this->save();
            \Log::info(__FILE__.":".__LINE__);
    }

    public function __toString(){
        return isset($this->attributes['email']) ? $this->attributes['email'] : "";
    }
}
