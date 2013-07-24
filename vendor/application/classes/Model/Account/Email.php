<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Email extends Model_Master {

    protected $_table_name = 'account_email';
    protected $_db_group = 'mship';
    protected $_primary_key = 'id';
    protected $_table_columns = array(
        'id' => array('data_type' => 'string'),
        'account_id' => array('data_type' => 'bigint'),
        'email' => array('data_type' => 'string'),
        'primary' => array('data_type' => 'boolean', 'is_nullable' => TRUE),
        'verified' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'created' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'deleted' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
    );
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    // Belongs to relationships
    protected $_belongs_to = array(
        'account' => array(
            'model' => 'Account',
            'foreign_key' => 'account_id',
        ),
    );
    // Has man relationships
    protected $_has_many = array();
    // Has one relationship
    protected $_has_one = array();

    // Validation rules
    public function rules() {
        return array(
            'email' => array(
                array('not_empty'),
                array('email', array(':value', true)),
                array(array($this, 'email_check_unique')),
            ),
        );
    }

    // Data filters
    public function filters() {
        return array(
            'email' => array(
                array('trim'),
                array('strtolower'),
            )
        );
    }

    // Check email is unique in the database.
    public function email_check_unique($email) {
        return !(bool) ORM::factory("Account_Email")
                        ->where("id", "!=", $this->id)
                        ->where("email", "=", $email)
                        ->where("deleted", "=", NULL)
                        ->count_all() > 0;
    }

    // Gotta love __toString!
    public function __toString() {
        return ($this->email ? $this->email : "");
    }

}

?>