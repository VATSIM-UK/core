<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Test extends Minion_Task
{
    protected $_options = array(
        "debug" => false,
    );
    
    protected function _execute(array $params)
    {
        ob_end_flush();
        define("AUTH_OVERRIDE", true);
        print Helper_Account_Main::check_login_status();
    }
}