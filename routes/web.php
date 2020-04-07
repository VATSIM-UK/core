<?php

use App\Libraries\VatsimApi;
use GuzzleHttp\Client;

$client = (new Client);

$r = (new VatsimApi($client))->ratings();

dd($r);

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-public.php');
});

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-main.php');
});

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-admin.php');
});
