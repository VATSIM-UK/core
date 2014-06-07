<?php

defined('SYSPATH') OR die('No direct access allowed.');

/* * ************************************************************
 * Default Router
 * ************************************************************ */
Route::set('error_route', 'error(/)(<action>(/)(<error>(/)))')
        ->defaults(array(
            'directory' => '',
            'controller' => 'Error',
            'action' => 'generic',
            'error' => NULL,
        ));
Route::set('frontend_system_route', '<directory>(/)(<controller>(/)(<action>(/)(<area>(/))))',
        array(
            "directory" => "(sso|mship)",
        ))
        ->defaults(array(
            'directory' => 'Sso',
            'controller' => 'Manage',
            'action' => 'display',
            'area' => NULL,
        ));
Route::set('default', '')
        ->defaults(array(
            'directory' => 'Sso',
            'controller' => 'Manage',
            'action' => 'display'
        ));
