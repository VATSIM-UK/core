<?php

function format_name($name){
    $name = trim($name);

    if ($name == strtoupper($name) || $name == strtolower($name)) {
        $name = ucwords(strtolower($name));
    }

    return $name;
}

function sanitize_email($email){
    return strtolower(trim($email));
}

function is_date_string($suspectedDateString){
    return preg_match("/^(\d{4})\-(\d{2})\-(\d{2})$/i", $suspectedDateString);
}

function is_relative_date_string($suspectedRelativeDateString){
    try {
        \Carbon\Carbon::parse($suspectedRelativeDateString, "UTC");
        return true;
    } Catch(Exception $e) {
        return false;
    }
}

function human_diff_string(\Carbon\Carbon $ts1, \Carbon\Carbon $ts2, $absolute = false){
    if(!$ts1 or !$ts2){
        return "unknown length";
    }

    $diff = $ts1->diff($ts2, $absolute);

    $units = [];

    if ($diff->y > 0) {
        $units[] = $diff->y . " " . str_plural("hour", $diff->y);
    }

    if ($diff->m > 0) {
        $units[] = $diff->m . " " . str_plural("month", $diff->m);
    }

    if ($diff->d > 0) {
        $units[] = $diff->d . " " . str_plural("day", $diff->d);
    }

    if ($diff->h > 0) {
        $units[] = $diff->h . " " . str_plural("hour", $diff->h);
    }

    if(count($units) == 1){
        return $units[0];
    }

    $lastElement = array_pop($units);
    $unitsString = implode(",", $units) . " and ".$lastElement;

    return $unitsString;
}