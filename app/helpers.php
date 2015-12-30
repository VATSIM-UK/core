<?php

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