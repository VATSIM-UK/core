<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Sys_Token extends Model_Master {

    protected $_db_group = 'sys';
    protected $_table_name = 'token';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'account_id' => array('data_type' => 'int'),
        'extra_id' => array('data_type' => 'bigint'),
        'code' => array('data_type' => 'varchar'),
        'type' => array('data_type' => 'smallint'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'expires' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'used' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'enabled' => array('data_type' => 'boolean'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'account' => array(
            'model' => 'Account_Main',
            'foreign_key' => 'account_id',
        ),
    );
    
    // Has man relationships
    protected $_has_many = array();
    
    // Has one relationship
    protected $_has_one = array(
    );
    
    // Validation rules
    public function rules(){
        return array(
        );
    }
    
    // Data filters
    public function filters(){
        return array();
    }
    
    /**
     * Generate a new token for the given account.
     * 
     * @param int $account_id The account ID to generate a token for.
     * @param int $type The type of token to generate.
     * @param int $lifetime The lifetime in seconds.
     * @param int $extra_id Any extra IDs associated with this token.
     * @return Model_Sys_Token The token class itself.
     */
    public function action_generate($account_id, $type, $lifetime=172800, $extra_id=0){
        $account = ORM::factory("Account_Main", $account_id);
        if(!$account->loaded()){
            return;
        }
        
        // Let's disable all outstanding ones!
        $old = $account->tokens->where("enabled", "=", 1)->where("type", "=", $type)->where("extra_id", "=", $extra_id)->find_all();
        foreach($old as $o){
            $o->enabled = 0;
            $o->save();
        }
        
        // Let's create!
        $this->account_id = $account_id;
        $this->extra_id = $extra_id;
        $this->code = uniqid(Text::random("alnum", 10)."-");
        $this->type = $type;
        $this->created = gmdate("Y-m-d H:i:s");
        $this->expires = gmdate("Y-m-d H:i:s", strtotime("+".$lifetime." seconds"));
        $this->enabled = 1;
        $this->save();
        
        ORM::factory("Account_Note")->writeNote($account, "SYSTEM/TOKEN_GENERATED", $account_id, array(Enum_System_Token::valueToType($type), $this->code), Enum_Account_Note_Type::AUTO);

        return $this;
    }
        
    /**
     * Consume a token by passing the correct parameters.
     * 
     * @param string $code The token code to consume.
     * @return Sys_Token The token that's now expired.
     */
    public function action_consume($code){
        // Try and find the right reset....
        $reset = $this->where("code", "=", $code)
                    ->where("expires", "<=", gmdate("Y-m-d H:i:s"))
                    ->where("enabled", "=", 1)
                    ->find();
        
        if(!$reset->loaded()){
            return false;
        }
        
        // Expire this link!
        $reset->enabled = 0;
        $reset->used = gmdate("Y-m-d H:i:s");
        $reset->save();
        
        return $reset;
    }
}

?>