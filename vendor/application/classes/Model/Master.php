<?php

defined('SYSPATH') or die('No direct script access.');

abstract class Model_Master extends ORM {
    public function changed($field = NULL){
        // If a field is set, let's use the parent!
        if($field != null && parent::changed($field)){
            $_old = $this;
            $_old->reset();
            return array("old" => $_old->{$field}, "new" => $this->{$field});
        }
        
        // Get the changed fields.
        $changedFields = parent::changed(null);
        
        // Now, duplicate THIS class so we can reset it.
        $_old = clone $this;
        $_old->reload();
        
        // Now, let's see what's REALLY changed!
        foreach($changedFields as $key => $value){
            $changedFields[$key] = array();
            $changedFields[$key]["old"] = $_old->{$key};
            $changedFields[$key]["new"] = $this->{$key};
        }
        
        // Return the changed fields
        return $changedFields;
    }
}

?>