<?php

defined('SYSPATH') OR die('No direct access allowed.');

return array
    (
    'default' => array(
        'type' => 'MySQL',
        'connection' => array(
            'hostname' => 'localhost',
            'database' => 'core',
            'username' => "core",
            'password' => "rww2ZnyePqQ9FJyR",
            'persistent' => TRUE,
        ),
        'table_prefix' => '',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'site' => array(
        'type' => 'MySQL',
        'connection' => array(
            'hostname' => 'localhost',
            'database' => 'core',
            'username' => "core",
            'password' => "rww2ZnyePqQ9FJyR",
            'persistent' => TRUE,
        ),
        'table_prefix' => 'site_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'mship' => array(
        'type' => 'MySQL',
        'connection' => array(
            'hostname' => 'localhost',
            'database' => 'core',
            'username' => "core",
            'password' => "rww2ZnyePqQ9FJyR",
            'persistent' => TRUE,
        ),
        'table_prefix' => 'mship_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'sso' => array(
        'type' => 'MySQL',
        'connection' => array(
            'hostname' => 'localhost',
            'database' => 'core',
            'username' => "core",
            'password' => "rww2ZnyePqQ9FJyR",
            'persistent' => TRUE,
        ),
        'table_prefix' => 'sso_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'train' => array(
        'type' => 'MySQL',
        'connection' => array(
            'hostname' => 'localhost',
            'database' => 'core',
            'username' => "core",
            'password' => "rww2ZnyePqQ9FJyR",
            'persistent' => TRUE,
        ),
        'table_prefix' => 'train_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
    'sys' => array(
        'type' => 'MySQL',
        'connection' => array(
            'hostname' => 'localhost',
            'database' => 'core',
            'username' => "core",
            'password' => "rww2ZnyePqQ9FJyR",
            'persistent' => TRUE,
        ),
        'table_prefix' => 'sys_',
        'charset' => 'utf8',
        'caching' => TRUE,
    ),
);
