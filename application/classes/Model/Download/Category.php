<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Download_Category extends Model_Master {

    protected $_db_group = 'site';
    protected $_table_name = 'download_category';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'medint'),
        'name' => array('data_type' => 'varchar'),
        'description' => array('data_type' => 'int'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );

    // Belongs to relationships
    protected $_belongs_to = array();
    
    // Has man relationships
    protected $_has_many = array(
        'versions' => array(
            'model' => 'Download_Version',
            'foreign_key' => 'download_id',
        ),
        'categories' => array(
            'model' => 'Download_Category',
            'through' => 'Download_To_Category',
        )
    );
    
    // Has one relationship
    protected $_has_one = array(
    );
    
    // Validation rules
    public function rules(){
        return array(
            'name' => array(
                array('not_empty'),
            ),
            'description' => array(
                array('not_empty'),
            ),
        );
    }
    
    // Data filters
    public function filters(){
        return array(
            'name' => array(
                array('trim'),
                array(array("UTF8", "clean"), array(":value")),
            ),
            'description' => array(
                array('trim'),
                array(array("UTF8", "clean"), array(":value")),
            ),
        );
    }
}

?>