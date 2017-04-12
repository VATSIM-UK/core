<?php

Route::group([
    'as' => 'networkdata.api.',
    'namespace' => 'Api',
    'domain' => config('app.url'),
    'prefix' => 'network-data',
], function () {
    Route::get('/online', [
        'as' => 'online',
        'uses' => 'Feed@getOnline',
    ]);
});
