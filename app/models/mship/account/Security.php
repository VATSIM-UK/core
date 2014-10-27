<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Carbon\Carbon;
use \Models\Mship\Security as SecurityType;

class Security extends \Eloquent {

	use SoftDeletingTrait;
        protected $table = "mship_account_security";
        protected $primaryKey = "account_security_id";
        protected $dates = ['created_at', 'deleted_at'];
        protected $hidden = ['account_security_id'];

        public function account(){
            return $this->belongsTo("\Models\Mship\Account\Account", "account_id", "account_id");
        }

        public function security(){
            return $this->belongsTo("\Models\Mship\Security", "security_id", "security_id");
        }

        public function setValueAttribute($value){
            $this->attributes['value'] = $this->hash($value);
        }

        public function verifyPassword($value){
            return $this->value == $this->hash($value);
        }

        public function getIsActiveAttribute(){
            if($this->expires_at == NULL){ return true; }
            elseif($this->value == ''){ return false; }
            else { return Carbon::createFromFormat("Y-m-d H:i:s", $this->expires_at)->diffInDays() > 0; }
        }

        private function hash($value){
            return sha1(sha1($value));
        }

        public function save(Array $options = array()){
            // Set the expiry date!
            if($this->expires_at == NULL OR $this->expires_at == '0000-00-00 00:00:00'){
                $securityType = SecurityType::find($this->type);
                if($securityType AND $securityType->expires > 0){
                    $this->attributes['expires_at'] = Carbon::now()->addDays($securityTypes->expires)->toDateTimeString();
                }
            }

            parent::save();
        }
}
