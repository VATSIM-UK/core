<?php

return array(

	'driver' => 'fcache',
	'path' => storage_path().'/cache',
	'connection' => null,
	'table' => 'cache',
	'memcached' => array(

		array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100),

	),
	'prefix' => 'laravel',
);
