<?php

defined('SYSPATH') OR die('No direct access allowed.');

/* * ************************************************************
 * Default Router
 * ************************************************************ */
Route::set('frontend_system_route', '<directory>(/)(<controller>(/)(<action>(/)(<area>(/))))',
        array(
            "directory" => "(sso)",
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
