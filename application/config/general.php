<?php

defined('SYSPATH') OR die('No direct access allowed.');

return array(
    'session_name' => 'vuk_auth',
    'site_title' => 'VATSIM United Kingdom',
    'system_user' => 707070,
    'system_version' => exec("git describe --abbrev=0 --tags"),
    'system_version_date' => gmdate("d/m/y H:i \G\M\T", filemtime(realpath(APPPATH."../.git/"))),
);
