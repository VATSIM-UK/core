<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Main {
    public static function valueToType($id) {
        $constants = new ReflectionClass(get_called_class());
        foreach ($constants->getConstants() as $c => $v) {
            if ($v == $id) {
                return $c;
            }
        }
        return $id;
    }

    public static function IdToValue($str) {
        $constants = new ReflectionClass(get_called_class());
        foreach ($constants->getConstants() as $c => $v) {
            if ($c == strtoupper($str)) {
                return $v;
            }
        }
        return $str;
    }

    public static function getAll(){
        $constants = new ReflectionClass(get_called_class());
        return $constants->getConstants();
    }
    
    public static function valueExists($value){
        return self::valueToType($value) != $value;
    }
    
    public static function keyExists($key){
        return self::IdToValue($key) != $key;
    }
}