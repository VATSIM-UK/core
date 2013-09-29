<?php

defined('SYSPATH') or die('No direct script access.');

class Helper_Session {
    const SESSION_INSTANCE = "database";
    
    /**
     * Helper to set the session data.
     * 
     * @param mixed $k The Key of the session data to store.
     * @param mixed $v The value of the session data.
     * @return void
     */
    public static function set($k, $v){
        Session::instance(self::SESSION_INSTANCE)->set($k, $v);
    }
    
    /**
     * Helper to get the session data.
     * 
     * @param mixed $k The key of the session data to store.
     * @param mixed $d The default value to return if not set.
     * @return mixed Either the value of {@link $k} or {@link $d}
     */
    public static function get($k, $d=null){
        Session::instance(self::SESSION_INSTANCE)->get($k, $d);
    }
}

?>