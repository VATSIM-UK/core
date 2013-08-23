<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Main {
    public static function valueToType($value) {
        $constants = new ReflectionClass(get_called_class());
        foreach ($constants->getConstants() as $c => $v) {
            if ($v == $value) {
                return $c;
            }
        }
        return $value;
    }

    public static function IdToValue($idString) {
        $constants = new ReflectionClass(get_called_class());
        foreach ($constants->getConstants() as $c => $v) {
            if ($c == strtoupper($idString)) {
                return $v;
            }
        }
        return $idString;
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