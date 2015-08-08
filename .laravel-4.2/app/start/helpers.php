<?php

function binary_search($needle, $haystack, $key, $left, $right) {
    if ($left > $right) {
        return false;
    }

    $mid = (int) (($left + $right) / 2);

    // Get the keyValue
    $keyValue = is_object($haystack[$mid]) ? $haystack[$mid]->{$key} : $haystack[$mid][$key];

    if ($keyValue == $needle) {
        return true;
    } elseif ($keyValue > $needle) {
        return binary_search($needle, $haystack, $key, $left, $mid - 1);
    } elseif ($keyValue < $needle) {
        return binary_search($needle, $haystack, $key, $mid + 1, $right);
    }
}
