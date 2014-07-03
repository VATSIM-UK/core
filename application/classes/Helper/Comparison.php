<?php

defined('SYSPATH') or die('No direct script access.');

class Helper_Comparison {
    public static function between_both_incl($val, $lower, $upper){
        return ($val >= $lower && $val <= $upper);
    }
    public static function between_lower_incl($val, $lower, $upper){
        return ($val >= $lower && $val < $upper);
    }
    public static function between_upper_incl($val, $lower, $upper){
        return ($val > $lower && $val <= $upper);
    }
    public static function between_not_incl($val, $lower, $upper){
        return Helper_Comparison::between($val, $lower, $upper);
    }
    public static function between($val, $lower, $upper){
        return ($val > $lower && $val < $upper);
    }
}

?>