<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Sso_Token extends Model_Master {

    protected $_db_group = 'sso';
    protected $_table_name = 'token';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'token' => array('data_type' => 'varchar'),
        'sso_key' => array('data_type' => 'varchar'),
        'return_url' => array('data_type' => 'varchar'),
        'account_id' => array('data_type' => 'int'),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'expires' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'account' => array(
            'model' => 'Account',
            'foreign_key' => 'account_id',
        ),
    );
    
    // Has man relationships
    protected $_has_many = array();
    
    // Has one relationship
    protected $_has_one = array();
    
    // Validation rules
    public function rules(){
        return array();
    }
    
    // Data filters
    public function filters(){
        return array();
    }
    
    /**
     * Load the current or requested token.
     * 
     * @param string $token If set, this token will be loaded.
     * @return Sso_Token ORM Object.
     */
    public function get_current_token($token=null){
        if($token == null){
            $token = Session::instance(ORM::factory("Setting")->getValue("system.session.type"))->get(ORM::factory("Setting")->getValue("sso.token.key"), null);
            if($token == NULL){
                return $this;
            }
        }
        
        // Load it!
        $token = ORM::factory("Sso_Token")
                    ->where("token", "=", $token)
                    ->where("expires", ">", gmdate("Y-m-d H:i:s"))
                    ->limit(1)
                    ->find();
        
        // Now, load THIS model properly!
        $this->__construct($token->id);
        return $this;
    }
    
    /**
     * Start a new token for the current request.
     * 
     * This will also kill all old tokens if the session is currently set.
     * 
     * @param string $token The token to start.
     * @param string $key The section/area/module key.
     * @param string $returnURL The URL to return the member to.
     * @param boolean True on success, false otherwise.
     */
    public function set_current_token($token, $key, $returnURL=null){
        // First, let's kill the old token if it's currently set.
        $oldToken = $this->get_current_token();
        if($oldToken->loaded()){
            $oldToken->expires = gmdate("Y-m-d H:i:s", strtotime("-30 seconds"));
            $oldToken->save();
        }
        Session::instance(ORM::factory("Setting")->getValue("system.session.type"))->delete(ORM::factory("Setting")->getValue("sso.token.key"));
        
        // Now, start a new one!
        $newToken = ORM::factory("Sso_Token");
        $newToken->token = $token;
        $newToken->sso_key = $key;
        $newToken->return_url = $returnURL;
        $newToken->created = gmdate("Y-m-d H:i:s");
        $newToken->expires = gmdate("Y-m-d H:i:s", strtotime("+".ORM::factory("Setting")->getValue("sso.token.expires")));
        $newToken->save();
        
        // Store this token!
        Session::instance(ORM::factory("Setting")->getValue("system.session.type"))->set(ORM::factory("Setting")->getValue("sso.token.key"), $token);
        
        return $newToken;
    }
}

?>