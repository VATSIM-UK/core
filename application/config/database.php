<?php

defined('SYSPATH') OR die('No direct access allowed.');

// Determine which database's we should be using.
$useDB = (Kohana::$environment !== Kohana::PRODUCTION) ? "dev" : "prod";

// Differing DB settings
$dbOvr = array(
    "dev" => array(
        'connection' => array(
            'hostname' => 'callisto.vatsim-uk.co.uk',
            'database' => 'dev_core',
            'username' => 'dev',
            'password' => 'Monk1esW111T4k3ov3R!',
            'persistent' => TRUE,
        ),
    ),
    "prod" => array(
        'connection' => array(
            'hostname' => 'callisto.vatsim-uk.co.uk',
            'database' => 'prod_core',
            'username' => 'prod_core',
            'password' => '4S9twpMG239N34MH',
            'persistent' => TRUE,
        ),
    ),
);

// Default DB settings
$dbDefs = array(
    'default' => array(
        'type' => 'MySQLi',
        'connection' => $dbOvr[$useDB]['connection'],
        'table_prefix' => '',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'site' => array(
        'type' => 'MySQLi',
        'connection' => $dbOvr[$useDB]['connection'],
        'table_prefix' => 'site_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'mship' => array(
        'type' => 'MySQLi',
        'connection' => $dbOvr[$useDB]['connection'],
        'table_prefix' => 'mship_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'sso' => array(
        'type' => 'MySQLi',
        'connection' => $dbOvr[$useDB]['connection'],
        'table_prefix' => 'sso_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'train' => array(
        'type' => 'MySQLi',
        'connection' => $dbOvr[$useDB]['connection'],
        'table_prefix' => 'train_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'stats' => array(
        'type' => 'MySQLi',
        'connection' => $dbOvr[$useDB]['connection'],
        'table_prefix' => 'stats_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'sys' => array(
        'type' => 'MySQLi',
        'connection' => $dbOvr[$useDB]['connection'],
        'table_prefix' => 'sys_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
);

return $dbDefs;
