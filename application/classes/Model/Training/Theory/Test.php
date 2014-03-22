<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Theory_Test extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'theory_test';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'sys_id' => array('data_type' => 'string'),
        'name' => array('data_type' => 'string'),
        'version' => array('data_type' => 'smallint'),
        'time_allowed' => array('data_type' => 'smallint'),
        'time_expire_action' => array('data_type' => 'string'),
        'retake_cooloff' => array('data_type' => 'tinyint'),
        'retake_max' => array('data_type' => 'tinyint'),
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
        'attempts' => array(
            'model' => 'Training_Theory_Attempt',
            'foreign_key' => 'test_id',
        ),
        'categories' => array(
            'model' => 'Training_Theory_Test_Category',
            'foreign_key' => 'test_id',
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
    
    public function get_all_tests(){
        return $this->where("deleted", "=", "0");
    }
    
    public function add_test($name, $options=array()){
        if($this->loaded()){
            return false;
        }
        
        // Make a test!
        $this->name = $name;
        
        // "Real" new, or fake new?
        if(!isset($options["version"])){
            $this->sys_id = strtoupper(strrev(uniqid()));
            $this->version = 1;
        }
        
        $this->save();
        $this->edit_test($options, true);
        
        return $this;
    }
    
    public function edit_test($options=array(), $inhibitVersioning=false){
        if(!is_array($options)){
            return false;
        }
        
        // Only one update? Available/deleted?
        if(count($options) == 1 AND (isset($options["deleted"]) OR isset($options["available"]))){
            $inhibitVersioning = 1;
        }
        
        // If we're not ignoring the creation rule, we must preserve this data!
        if($inhibitVersioning){
            foreach ($this->table_columns() as $key => $value) {
                if (isset($options[$key])) {
                    $this->{$key} = $options[$key];
                }
            }
            $this->save();
        } else {
            // Let's make a new one!
            $name = isset($options["name"]) ? $options["name"] : $this->name;
            $opt = array();
            foreach($this->table_columns() as $key => $value){
                if(isset($options[$key])){
                    $opt[$key] = $options[$key];
                } else {
                    $opt[$key] = $this->{$key};
                }
            }
            unset($opt["id"]);
            $opt["version"] = $this->version+1;
            $newTest = ORM::factory("Training_Theory_Test")->add_test($name, $opt);

            $this->edit_test(array("deleted" => 1), true);
            return $newTest;
        }
        
        return $this;
    }
    
    public function edit_test_categories($categories=array()){
        if(!is_array($categories)){
            return false;
        }
        // Delete all categories - we'll add them back in a sec.
        foreach($this->categories->find_all() as $c){
            $c->delete();
        }
        // Now add categories!
        foreach($categories as $c){
            $cat = ORM::factory("Training_Theory_Test_Category");
            $cat->test_id = $this->id;
            $cat->category_id = $c["category_id"];
            $cat->difficulty_min = $c["difficulty_min"];
            $cat->difficulty_max = $c["difficulty_max"];
            $cat->question_count = $c["question_count"];
            $cat->save();
        }
        
        return true;
    }
    
    public function get_question_count(){
        $count = 0;
        foreach($this->categories->find_all() as $c){
            $count+= $c->question_count;
        }
        return $count;
    }
}

?>