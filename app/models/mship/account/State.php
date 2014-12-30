<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class State extends \Eloquent {

	use SoftDeletingTrait;
        protected $table = "mship_account_state";
        protected $primaryKey = "account_state_id";
        protected $dates = ['created_at', 'deleted_at'];
        protected $fillable = ['state'];
        protected $hidden = ['account_state_id'];

        public function account(){
            return $this->belongsTo("\Models\Mship\Account\Account", "account_id", "account_id");
        }

        public function getLabelAttribute(){
            return \Enums\Account\State::getDescription($this->state);
        }

        public function __toString(){
            return $this->getLabelAttribute();
        }

        public function save(array $options = array()){
            // Check it doesn't exist, first!
            $check = State::where("account_id", "=", $this->account->account_id)->where("state", "=", $this->state);
            if($check->count() > 0){
                return $check->get();
            }

            parent::save($options);

            $deleteOld = State::where("account_id", "=", $this->account->account_id)->where("state", "!=", $this->state)->get();
            foreach($deleteOld as $do){
                $do->delete();
            }

            return $this;
        }
}
