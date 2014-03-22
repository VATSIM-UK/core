<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Training_Theory_Question extends Model_Master {

    protected $_db_group = 'train';
    protected $_table_name = 'theory_question';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'sys_id' => array('data_type' => 'string'),
        'question' => array('data_type' => 'string'),
        'version' => array('data_type' => 'smallint'),
        'category_id' => array('data_type' => 'int'),
        'type' => array('data_type' => 'int'),
        'answer_a' => array('data_type' => 'string'),
        'answer_b' => array('data_type' => 'string'),
        'answer_c' => array('data_type' => 'string'),
        'answer_d' => array('data_type' => 'string'),
        'answer_correct' => array('data_type' => 'string'),
        'difficulty_rating' => array('data_type' => 'smallint'),
        'used_count' => array('data_type' => 'bigint'),
        'used_last' => array('data_type' => 'timestamp'),
        'available' => array('data_type' => 'boolean'),
        'deleted' => array('data_type' => 'boolean'),
    );
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    // Belongs to relationships
    protected $_belongs_to = array(
        'category' => array(
            'model' => 'Training_Category',
            'foreign_key' => 'category_id',
        ),
    );
    // Has many relationships
    protected $_has_many = array(
    );
    // Has one relationship
    protected $_has_one = array(
    );

    // Validation rules
    public function rules() {
        return array();
    }

    // Data filters
    public function filters() {
        return array();
    }

    public function get_all_questions() {
        return $this->where("deleted", "=", 0)->find_all();
    }

    public function add_question($options = array()) {
        if($this->loaded()){
            return false;
        }
        
        // "REal" new, or fake new?
        if (!isset($options["version"])) {
            $this->sys_id = strtoupper(strrev(uniqid()));
            $this->version = 1;
        }

        return $this->edit_question($options, true);
    }

    public function edit_question($options = array(), $inhibitVersioning = false) {
        if (!is_array($options)) {
            return false;
        }

        if ($inhibitVersioning) {
            foreach ($this->table_columns() as $key => $value) {
                if (isset($options[$key])) {
                    $this->{$key} = $options[$key];
                }
            }
            $this->save();
        } else {
            foreach ($this->table_columns() as $key => $value) {
                if (isset($options[$key])) {
                    $opt[$key] = $options[$key];
                } else {
                    $opt[$key] = $this->{$key};
                }
            }
            unset($opt["id"]);
            $opt["version"] = $this->version + 1;
            $newQuestion = ORM::factory("Training_Theory_Question")->add_question($opt);
            
            $this->edit_question(array("deleted" => 1), true);
            return $newQuestion;
        }

        return $this;
    }

}

?>