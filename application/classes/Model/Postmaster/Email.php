<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Postmaster_Email extends Model_Master {

    protected $_db_group = 'sys';
    protected $_table_name = 'postmaster_email';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'key' => array('data_type' => 'varchar'),
        'template' => array('data_type' => 'varchar'),
        'layout' => array('data_type' => 'varchar'),
        'subject' => array('data_type' => 'varchar'),
        'body' => array('data_type' => 'varchar'),
        'reply_to' => array('data_type' => 'varchar'),
        'priority' => array('data_type' => 'smallint'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
    );
    
    // Has man relationships
    protected $_has_many = array(
        'queued_emails' => array(
            'model' => 'Postmaster_Queue',
            'foreign_key' => 'email_id',
        )
    );
    
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
        return array(
        );
    }
    
    public function getTemplate(){
        if($this->template == "" OR $this->template == "Default"){
            return ORM::factory("Setting")->getValue("system.postmaster.email.template");
        } else {
            return $this->template;
        }
    }
    
    public function getLayout(){
        if($this->layout == "" OR $this->layout == "Default"){
            return ORM::factory("Setting")->getValue("system.postmaster.email.layout");
        } else {
            return $this->layout;
        }
    }
}

?>