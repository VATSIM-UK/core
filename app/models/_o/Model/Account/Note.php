<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Note extends Model_Master {
   
    // Get the text for the note
    public function __toString(){
        return $this->body;
    }
    
    /**
     * Create a note on a member's account.
     * 
     * 
     */
    public function writeNote($account, $format, $user=707070, $data=array(), $type=Enum_Account_Note_Type::SYSTEM, $date=null){
        // If account isn't of type Model_Account, error.
        if(!$account instanceof Model_Account_Main){
            throw new Kohana_Exception("'account' must be of type Model_Account");
            die("HAIRAI!");
            return false;
        }
        
        // Try and find this format
        $format = ORM::factory("Account_Note_Format")->where(DB::expr("UPPER(CONCAT(`section`, '/', `action`))"), "=", $format)
                                                     ->order_by("version", "DESC")
                                                     ->limit(1)
                                                     ->find();
        
        // If format isn't of type Model_Account_Note_Format, error.
        if(!$format instanceof Model_Account_Note_Format){
            throw new Kohana_Exception("'format' must be of type Model_Account_Note_Format");
            die("HAIRsgsdfAI!");
            return false;
        }
        
        // If the format isn't loaded, error.
        if(!$format->loaded()){
            //die("NO FORMAT:".$format);
            return false;
        }
        
        // We need to see how many "variables" we've got in our format string.
        preg_match_all('/(\%[a-zA-Z]|\%[0-9]+\$[a-zA-Z])/i', $format->string, $_m);
        $_data = array_merge(array($format->section, $format->action), $data);
        if(count($_data)-count($_m) > 0){
            $_data = array_merge($_data, array_fill(count($_data), count($_data)-count($_m), "unknown"));
        }
        
        // Let's find/add this user's details.
        $_user = ORM::factory("Account_Main", $user);
        if(!$_user->loaded()){
            $_user = ORM::factory("Account_Main", Kohana::$config->load('general')->get("system_user"));
        }
        
        foreach($_user->list_columns() as $_col => $_val){
            $format->string = str_replace("{user_".$_col."}", $_user->{$_col}, $format->string);
        }
        
        // Let's create a note!
        $_ormAccountNote = ORM::factory("Account_Note");
        $_ormAccountNote->account_id = $account;
        $_ormAccountNote->actioner_id = $_user;
        $_ormAccountNote->format_id = $format;
        $_ormAccountNote->type = $type;
        $_ormAccountNote->created = ($date != null) ? $date : gmdate("Y-m-d H:i:s");
        $_ormAccountNote->body = vsprintf($format->string, $_data);
        $_ormAccountNote->data = $data;
        $_ormAccountNote->ip = ip2long(Arr::get($_SERVER, "REMOTE_ADDR", "127.0.0.1"));
        $_ormAccountNote->save();
        
        return $_ormAccountNote->saved();
    }
}

?>
