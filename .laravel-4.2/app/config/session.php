<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Default Session Driver
	|--------------------------------------------------------------------------
	|
	| This option controls the default session "driver" that will be used on
	| requests. By default, we will use the lightweight native driver but
	| you may specify any of the other wonderful drivers provided here.
	|
	| Supported: "file", "cookie", "database", "apc",
	|            "memcached", "redis", "array"
	|
	*/

	'driver' => 'database',

	'lifetime' => 86400*2, // 2 days.

	'expire_on_close' => false,

	'files' => storage_path().'/sessions',

	'connection' => "mysql",

	'table' => 'sys_sessions',

	'lottery' => array(20, 100),

	'cookie' => 'vatuk_core_session',

	'path' => '/',

	'domain' => null,

	'secure' => false,

);
