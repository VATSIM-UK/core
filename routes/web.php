<?php

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-main.php');
});

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-admin.php');
});

Route::group(['domain' => 'vats.im'], function () {
    require base_path('routes/web-shorturl.php');
});
