<?php

namespace Models;

use \Models\Sys\Timeline\Entry;

abstract class aModel extends \Eloquent {
    public function toArray(){
        $array = parent::toArray();
        $array['status'] = ($this->deleted_at ? "Deleted" : "Active");
        if(isset($array['pivot'])){
            unset($array['pivot']);
        }
        return $array;
    }
}
