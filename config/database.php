<?php

use Illuminate\Support\Arr;

$coreDb = [];
if (env('CORE_DATABASE_URL', null) !== null) {
    $split = parse_url(getenv('CORE_DATABASE_URL'));
    $coreDb['host'] = $split['host'];
    $coreDb['name'] = substr($split['path'], 1);
    $coreDb['port'] = $split['port'];
    $coreDb['user'] = $split['user'];
    $coreDb['pass'] = $split['pass'];
}

$ctsDb = [];
if (env('CTS_DATABASE_URL', null) !== null) {
    $split = parse_url(getenv('CTS_DATABASE_URL'));
    $ctsDb['host'] = $split['host'];
    $ctsDb['name'] = substr($split['path'], 1);
    $ctsDb['port'] = $split['port'];
    $ctsDb['user'] = $split['user'];
    $ctsDb['pass'] = $split['pass'];
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

    'default' => env('DB_CONNECTION', 'mysql'),

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

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_MYSQL_HOST', Arr::get($coreDb, 'host')),
            'port' => env('DB_MYSQL_PORT', Arr::get($coreDb, 'port')),
            'database' => env('DB_MYSQL_NAME', Arr::get($coreDb, 'name')),
            'username' => env('DB_MYSQL_USER', Arr::get($coreDb, 'user')),
            'password' => env('DB_MYSQL_PASS', Arr::get($coreDb, 'pass')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_MYSQL_CHARSET', 'utf8mb4'),
            'collation' => env('DB_MYSQL_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_MYSQL_PREFIX', ''),
            'strict' => true,
            'engine' => null,
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true,
            ],
        ],

        'cts' => [
            'driver' => 'mysql',
            'host' => env('DB_MYSQL_HOST', Arr::get($ctsDb, 'host')),
            'port' => env('DB_MYSQL_PORT', Arr::get($ctsDb, 'port')),
            'database' => env('CTS_DATABASE', Arr::get($ctsDb, 'name')),
            'username' => env('DB_MYSQL_USER', Arr::get($ctsDb, 'user')),
            'password' => env('DB_MYSQL_PASS', Arr::get($ctsDb, 'pass')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_MYSQL_CHARSET', 'utf8mb4'),
            'collation' => env('DB_MYSQL_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_MYSQL_PREFIX', ''),
            'strict' => true,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
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

        'client' => env('REDIS_CLIENT', 'predis'),

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            'read_write_timeout' => -1,
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
            'read_write_timeout' => -1,
        ],

        'session' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_SESSION_DB', 2),
            'read_write_timeout' => -1,
        ],

        'queue' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_QUEUE_DB', 3),
            'read_write_timeout' => -1,
        ],

    ],

];
