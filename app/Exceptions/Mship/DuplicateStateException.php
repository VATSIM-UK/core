<?php namespace App\Exceptions\Mship;

class DuplicateStateException extends \Exception {

    private $state;

    public function __construct($state){
        $this->state = $state;
    }

    public function __toString(){
        return "State " . \App\Models\Mship\Account\State::getStateKeyFromValue($this->state) . " already set on this account.";
    }
}