<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Download_Version extends Model_Master {

    protected $_db_group = 'site';
    protected $_table_name = 'download_version';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'medint'),
        'download_id' => array('data_type' => 'medint'),
        'version' => array('data_type' => 'varchar'),
        'whats_new' => array('data_type' => 'varchar'),
        'download_count' => array('data_type' => 'int'),
        'extension' => array('data_type' => 'varchar'),
        'created' => array('data_type' => 'timestamp'),
        'released' => array('data_type' => 'timestamp'),
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
            'version' => array(
                array('not_empty'),
            ),
            'whats_new' => array(
                array('not_empty'),
            ),
        );
    }
    
    // Data filters
    public function filters(){
        return array(
            'version' => array(
                array('trim'),
                array(array("UTF8", "clean"), array(":value")),
            ),
            'whats_new' => array(
                array('trim'),
                array(array("UTF8", "clean"), array(":value")),
            ),
        );
    }
}

?>