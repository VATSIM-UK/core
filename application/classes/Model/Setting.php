<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Setting extends Model_Master {

    protected $_db_group = 'sys';
    protected $_table_name = 'setting';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'area' => array('data_type' => 'varchar'),
        'key' => array('data_type' => 'varchar'),
        'value' => array('data_type' => 'varchar'),
        'default_value' => array('data_type' => 'varchar'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );

    // Belongs to relationships
    protected $_belongs_to = array(
    );
    
    // Has man relationships
    protected $_has_many = array(
    );
    
    // Has one relationship
    protected $_has_one = array(
    );
    
    // Validation rules
    public function rules(){
        return array(
            'area' => array(
                array('not_empty'),
            ),
            'key' => array(
                array('not_empty'),
            ),
            'value' => array(
            ),
        );
    }
    
    // Data filters
    public function filters(){
        return array(
            'area' => array(
                array('trim'),
                array('strtoupper'),
            ),
            'key' => array(
                array('trim'),
                array('strtoupper'),
            ),
        );
    }
    
    // Load a specific key.
    public function __construct($id = NULL) {
        parent::__construct(NULL);
        
        // Let's get the ID!
        if($id != null){
            $id = explode(".", $id);
            $id = $this->where("area", "=", $id[0])->where("key", "=", $id[1])->find();
            if($id->loaded()){
                parent::__construct($id->id);
            }
        }
    }
}

?>
