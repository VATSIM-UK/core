<?php namespace App\Exceptions\Mship;

use App\Models\Mship\State;

class StateDoesNotExistException extends \Exception {

    private $state;

    public function __construct(State $state){
        $this->state = $state;
    }

    public function __toString(){
        return "State is not valid.";
    }
}