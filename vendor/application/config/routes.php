<?php

defined('SYSPATH') OR die('No direct access allowed.');

/* * ************************************************************
 * Default Router
 * ************************************************************ */
Route::set('frontend_system_route', '<directory>(/)(<controller>(/)(<action>(/)(<area>(/))))',
        array(
            "directory" => "(membership|site|account|training|sso)",
        ))
        ->defaults(array(
            'directory' => 'membership',
            'controller' => 'default',
            'action' => NULL,
            'area' => NULL,
        ));
Route::set('frontend_global_route', '<controller>/<action>(/)(<area>)',
        array(
            "controller" => "(global|partial)",
        ))
        ->defaults(array(
            'controller' => 'global',
            'action' => NULL,
            'area' => NULL,
        ));
Route::set('frontend_site_route', '<controller>/<action>(/)(<area>)')
        ->defaults(array(
            'directory' => 'Site',
            'controller' => 'page',
            'action' => 'homepage',
        ));
Route::set('frontend_site_page_route', '<page>')
        ->defaults(array(
            'directory' => 'Site',
            'controller' => 'page',
            'action' => 'display',
        ));
Route::set('default', '')
        ->defaults(array(
            'directory' => 'Site',
            'controller' => 'page',
            'action' => 'default'
        ));/*
Route::set('frontend_page_default_route', 'page(/)')
        ->defaults(array(
            'directory' => 'Site',
            'controller' => 'page',
            'action' => 'homepage',
        ));
Route::set('frontend_page_route', 'page/<page>(/)')
        ->defaults(array(
            'directory' => 'Site',
            'controller' => 'page',
            'action' => 'display',
        ));
Route::set('frontend_route', '<controller>(/)(<action>(/)(<extra>(/)))')
        ->defaults(array(
            'directory' => 'Site',
            'controller' => 'page',
            'action' => 'default',
        ));
Route::set('default', '')
        ->defaults(array(
            'directory' => 'Site',
            'controller' => 'page',
            'action' => 'default'
        ));*/