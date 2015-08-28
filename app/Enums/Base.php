<?php

namespace App\Enums;

class Base {
    const CURRENT_VERSION = "1.2.4";

    public static function valueToKey($value) {
        $constants = new \ReflectionClass(get_called_class());
        foreach ($constants->getConstants() as $c => $v) {
            if ($v == $value) {
                return $c;
            }
        }
        return $value;
    }

    public static function keyToValue($idString) {
        $constants = new \ReflectionClass(get_called_class());
        foreach ($constants->getConstants() as $c => $v) {
            if ($c == strtoupper($idString)) {
                return $v;
            }
        }
        return $idString;
    }

    public static function getAll(){
        $constants = new \ReflectionClass(get_called_class());
        return $constants->getConstants();
    }

    public static function valueExists($value){
        return self::valueToKey($value) != $value;
    }

    public static function keyExists($key){
        return self::keyToValue($key) != $key;
    }

    public static function getDescription($value){
        return NULL;
    }
}