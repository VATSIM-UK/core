<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Content extends Model_Master {

    protected $_db_group = 'site';
    protected $_table_name = 'content';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'bigint'),
        'type' => array('data_type' => 'varchar', 'is_nullable' => TRUE),
        'parent_id' => array('data_type' => 'bigint', 'is_nullable' => TRUE),
        'name' => array('data_type' => 'varchar', 'is_nullable' => TRUE),
        'name_url' => array('data_type' => 'varchar', 'is_nullable' => TRUE),
        'content' => array('data_type' => 'varchar', 'is_nullable' => TRUE),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );

    // Belongs to relationships
    protected $_belongs_to = array(
        'parent' => array(
            'model' => 'Content',
            'foreign_key' => 'parent_id',
        )
    );
    
    // Has man relationships
    protected $_has_many = array(
        'children' => array(
            'model' => 'Content',
            'foreign_key' => 'parent_id',
        )
    );
    
    // Has one relationship
    protected $_has_one = array();
    
    // Validation rules
    public function rules(){
        return array(
            'name' => array(
                array('not_empty'),
            ),
            'name_url' => array(
                array('not_empty'),
            ),
            'content' => array(
                array('not_empty'),
            ),
        );
    }
    
    // Data filters
    public function filters(){
        return array(
            'name' => array(
                array('trim'),
                array('ucfirst'),
            ),
        );
    }
}

?>