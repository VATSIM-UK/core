<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Note extends Model_Master {

    protected $_db_group = 'mship';
    protected $_table_name = 'account_note';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'account_id' => array('data_type' => 'bigint'),
        'actioner_id' => array('data_type' => 'bigint'),
        'format_id' => array('data_type' => 'mediumint'),
        'type' => array('data_type' => 'tinyint'),
        'flag_id' => array('data_type' => 'bigint'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'body' => array('data_type' => 'varchar'),
        'data' => array('data_type' => 'varchar'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'user' => array(
            'model' => 'Account_Main',
            'foreign_key' => 'account_id',
        ),
        'actioner' => array(
            'model' => 'Account_Main',
            'foreign_key' => 'actioner_id',
        ),
    );
    
    // Has man relationships
    protected $_has_many = array();
    
    // Has one relationship
    protected $_has_one = array(
        'flag' => array(
            'model' => 'Account_Note_Flag',
            'foreign_key' => 'note_id',
        ),
        'format' => array(
            'model' => 'Account_Note_Format',
            'foreign_key' => 'note_id',
        )
    );
    
    // Validation rules
    public function rules(){
        return array();
    }
    
    // Data filters
    public function filters(){
        return array(
            'data' => array(
                array('serialize'),
            ),
        );
    }
    
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
            die("NO FORMAT:".$format);
            return false;
        }
        
        // We need to see how many "variables" we've got in our format string.
        preg_match_all('/(\%[a-zA-Z]|\%[0-9]+\$[a-zA-Z])/i', $format->string, $_m);
        $_data = array_merge(array($format->section, $format->action), $data);
        if(count($_data)-count($_m) > 0){
            $_data = array_merge($_data, array_fill(count($_data), count($_data)-count($_m), "unknown"));
        }
        
        // Let's find/add this user's details.
        $_user = ORM::factory("Account", $user);
        if(!$_user->loaded()){
            $_user = ORM::factory("Account", Kohana::$config->load('general')->get("system_user"));
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
        $_ormAccountNote->save();
        
        return $_ormAccountNote->saved();
    }
}

?>