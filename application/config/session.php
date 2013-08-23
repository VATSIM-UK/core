<?php

return array(
    'database' => array(
        'name' => 'vuk_core',
        'encrypted' => TRUE,
        'lifetime' => 43200,
        'group' => 'sys',
        'table' => 'session',
        'columns' => array(
            'session_id'  => 'id',
            'last_active' => 'last_active',
            'contents'    => 'contents'
        ),
        'gc' => 5000,
    ),
    'native' => array(
        'name' => 'vuk_core',
        'lifetime' => 60 * 60 * 8, // 8 hours
        'validate' => array("ip_address"),
        "regenerate" => 0,
    ),
);
?>