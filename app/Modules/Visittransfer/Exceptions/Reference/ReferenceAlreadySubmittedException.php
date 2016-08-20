<?php namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Models\Mship\Account;

class ReferenceAlreadySubmittedException extends \Exception {

    private $reference;

    public function __construct(Reference $reference){
        $this->reference = $reference;

        $this->message =  "You cannot re-submit a reference once it has already been submitted.";
    }

    public function __toString(){
        return $this->message;
    }
}