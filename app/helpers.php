<?php

function determine_mship_state_from_vatsim($region, $division)
{
    $states = \App\Models\Mship\State::orderBy('priority')->get();

    foreach ($states as $state) {
        $regionMatch = false;
        $divisionMatch = false;

        // We don't care about temps.
        if ($state->is_temporary) {
            continue;
        }

        if ($state->region->first() == '*') {
            $regionMatch = true;
        }

        if ($state->region->search($region) >= 0 && $state->region->search($region) !== false) {
            $regionMatch = true;
        }

        if ($state->division->first() == '*') {
            $divisionMatch = true;
        }

        if ($state->division->search($division) >= 0 && $state->division->search($division) !== false) {
            $divisionMatch = true;
        }

        if ($regionMatch && $divisionMatch) {
            return $state;
        }
    }

    return \App\Models\Mship\State::findByCode('UNKNOWN');
}

function format_name($name)
{
    $name = trim($name);

    if ($name == strtoupper($name) || $name == strtolower($name)) {
        $name = ucwords(strtolower($name));
    }

    return $name;
}

function sanitize_email($email)
{
    return strtolower(trim($email));
}

function is_date_string($suspectedDateString)
{
    return preg_match("/^(\d{4})\-(\d{2})\-(\d{2})$/i", $suspectedDateString);
}

function is_relative_date_string($suspectedRelativeDateString)
{
    try {
        \Carbon\Carbon::parse($suspectedRelativeDateString, 'UTC');

        return true;
    } catch (Exception $e) {
        return false;
    }
}

function human_diff_string(\Carbon\Carbon $ts1, \Carbon\Carbon $ts2, $absolute = false)
{
    if (!$ts1 or !$ts2) {
        return 'unknown length';
    }

    $diff = $ts1->diff($ts2, $absolute);

    $units = [];

    if ($diff->y > 0) {
        $units[] = $diff->y.' '.str_plural('hour', $diff->y);
    }

    if ($diff->m > 0) {
        $units[] = $diff->m.' '.str_plural('month', $diff->m);
    }

    if ($diff->d > 0) {
        $units[] = $diff->d.' '.str_plural('day', $diff->d);
    }

    if ($diff->h > 0) {
        $units[] = $diff->h.' '.str_plural('hour', $diff->h);
    }

    if (count($units) == 1) {
        return $units[0];
    }

    $lastElement = array_pop($units);
    $unitsString = implode(',', $units).' and '.$lastElement;

    return $unitsString;
}

function array_merge_concat($a1, $a2, $sep = ' ')
{
    $final_array = $a1;

    foreach ($a2 as $key => $value) {
        if (is_numeric($key)) {
            $final_array[] = $value;
        } elseif (array_key_exists($key, $a1)) {
            $final_array[$key] = $final_array[$key].$sep.$value;
        } else {
            $final_array[$key] = $value;
        }
    }

    return $final_array;
}
