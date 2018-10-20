<?php

$herokuDb = [];
if (env('DATABASE_URL', null) !== null) {
    $split = parse_url(getenv('DATABASE_URL'));
    $herokuDb['host'] = $split['host'];
    $herokuDb['name'] = substr($split['path'], 1);
    $herokuDb['port'] = $split['port'];
    $herokuDb['user'] = $split['user'];
    $herokuDb['pass'] = $split['pass'];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'core'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'core' => [
            'driver' => 'mysql',
            'host' => env('DB_MYSQL_HOST', array_get($herokuDb, 'host')),
            'port' => env('DB_MYSQL_PORT', array_get($herokuDb, 'port')),
            'database' => env('DB_MYSQL_NAME', array_get($herokuDb, 'name')),
            'username' => env('DB_MYSQL_USER', array_get($herokuDb, 'user')),
            'password' => env('DB_MYSQL_PASS', array_get($herokuDb, 'pass')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset'     => env('DB_MYSQL_CHARSET', 'utf8mb4'),
            'collation'   => env('DB_MYSQL_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix'      => env('DB_MYSQL_PREFIX', ''),
            'strict'      => true,
            'engine'      => null,
        ],

        'cts' => [
            'driver' => 'mysql',
            'host' => env('DB_MYSQL_HOST', array_get($herokuDb, 'host')),
            'port' => env('DB_MYSQL_PORT', array_get($herokuDb, 'port')),
            'database' => env('CTS_DATABASE'),
            'username' => env('DB_MYSQL_USER', array_get($herokuDb, 'user')),
            'password' => env('DB_MYSQL_PASS', array_get($herokuDb, 'pass')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_MYSQL_CHARSET', 'utf8mb4'),
            'collation' => env('DB_MYSQL_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_MYSQL_PREFIX', ''),
            'strict' => true,
            'engine' => null
          ],
      
        'pgsql' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
            'sslmode'  => 'prefer',
        ],

        'sqlsrv' => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

    ],

];
