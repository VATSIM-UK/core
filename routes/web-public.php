<?php

Route::group([
    'as' => 'site.',
    'namespace' => 'Site',
], function () {
    Route::get('/')->uses('HomePageController@view')->name('home');
});