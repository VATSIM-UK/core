<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Download_To_Category extends Model_Master {

    protected $_db_group = 'site';
    protected $_table_name = 'download_to_category';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'download_id' => array('data_type' => 'medint'),
        'category_id' => array('data_type' => 'medint'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );

    // Belongs to relationships
    protected $_belongs_to = array(
        'download' => array(
            'model' => 'Download',
            'foreign_key' => 'download_id',
        ),
        'category' => array(
            'model' => 'Download_Category',
            'foreign_key' => 'category_id',
        )
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
        );
    }
    
    // Data filters
    public function filters(){
        return array(
        );
    }
}

?>