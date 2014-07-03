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
Route::set('error_system_route', 'error/<action>(/)')
        ->defaults(array(
            'controller' => 'Error',
            'action' => 'unknown',
            'area' => NULL,
        ));
Route::set('frontend_website', '((<action>(/)(<area>(/))))')
        ->defaults(array(
            'controller' => 'Site',
            'action' => 'index',
            'area' => NULL,
        ));
Route::set('default', '')
        ->defaults(array(
            'directory' => 'Mship',
            'controller' => 'Manage',
            'action' => 'Display'
        ));
