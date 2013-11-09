<?php

defined('SYSPATH') OR die('No direct access allowed.');

/* * ************************************************************
 * Default Router
 * ************************************************************ */
Route::set('frontend_system_route', '<directory>(/)(<controller>(/)(<action>(/)(<area>(/))))',
        array(
            "directory" => "(sso|training)",
        ))
        ->defaults(array(
            'directory' => 'Sso',
            'controller' => 'Manage',
            'action' => 'display',
            'area' => NULL,
        ));
Route::set('default_training', 'training(/)(<controller>(/)(<action>(/)(<area>(/))))')
        ->defaults(array(
            'directory' => 'Training',
            'controller' => 'Course',
            'action' => 'dynamic_display'
        ));
Route::set('default', '')
        ->defaults(array(
            'directory' => 'Sso',
            'controller' => 'Manage',
            'action' => 'display'
        ));
