<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Notam extends Model_Master {

    protected $_db_group = 'site';
    protected $_table_name = 'notam';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'medint'),
        'category_id' => array('data_type' => 'medint'),
        'author_id' => array('data_type' => 'medint'),
        'title' => array('data_type' => 'varchar'),
        'stem' => array('data_type' => 'varchar'),
        'content' => array('data_type' => 'varchar'),
        'type' => array('data_type' => 'smallint'),
        'status' => array('data_type' => 'smallint'),
        'created' => array('data_type' => 'boolean'),
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
        'author' => array(
            'model' => 'Account',
            'foreign_key' => 'account_id',
        )
    );
    
    // Validation rules
    public function rules(){
        return array(
            'title' => array(
                array('not_empty'),
            ),
            'stem' => array(
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
            'title' => array(
                array('trim'),
                array('ucfirst'),
            ),
            'content' => array(
                array('trim'),
            ),
        );
    }
}

?>