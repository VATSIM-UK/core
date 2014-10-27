<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Category extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'category';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'name' => array('data_type' => 'string'),
        'available' => array('data_type' => 'boolean'),
        'deleted' => array('data_type' => 'boolean'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
    );
    
    // Has many relationships
    protected $_has_many = array(
        'questions' => array(
            'model' => 'Training_Theory_Question',
            'foreign_key' => 'category_id',
        ),
        'test_categories' => array(
            'model' => 'Training_Theory_Test_Category',
            'foreign_key' => 'category_id',
        ),
    );
    
    // Has one relationship
    protected $_has_one = array(
    );
    
    // Validation rules
    public function rules(){
        return array();
    }
    
    // Data filters
    public function filters(){
        return array();
    }
    
    public function find_all_categories(){
        return $this->where("deleted", "=", "0")->find_all();
    }
    
    public function add_category($name){
        if($this->loaded()){
            return false;
        }
        
        // Make a test!
        $this->name = $name;
        $this->save();
        
        return $this;
    }
    
    public function edit($options=array()){
        if(!is_array($options)){
            return false;
        }
        foreach($options as $key => $value){
            $this->{$key} = $value;
        }
        $this->save();
        return $this;
    }
}

?>