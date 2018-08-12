<?php

Route::group([
    'as' => 'site.',
    'namespace' => 'Site',
], function () {
    Route::group([
        'as' => 'atc.',
        'prefix' => 'atc',
    ], function () {
        Route::get('/')->uses('ATCPagesController@viewLanding')->name('landing');
        Route::get('/new-controller')->uses('ATCPagesController@viewNewController')->name('newController');
        Route::get('/progression-guide')->uses('ATCPagesController@viewProgressionGuide')->name('progression');
        Route::get('/endorsements')->uses('ATCPagesController@viewEndorsements')->name('endorsements');
        Route::get('/becoming-a-mentor')->uses('ATCPagesController@viewBecomingAMentor')->name('mentor');
    });
});
