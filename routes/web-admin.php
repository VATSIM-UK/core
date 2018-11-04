<?php

Route::group([
    'as' => 'training.waitingList.',
    'namespace' => 'Adm\Training',
    'prefix' => 'adm/training/waiting-list',
    'middleware' => ['auth_full_group'],
], function () {
    Route::get('/')->uses('WaitingListManagementController@index')->name('index');
    Route::get('/manage/{waitingList}')->uses('WaitingListManagementController@show')->name('show');
    Route::post('/manage/{waitingList}/add')->uses('WaitingListManagementController@store')->name('store');
    Route::post('/manage/{waitingList}/remove')->uses('WaitingListManagementController@destroy')->name('remove');
    Route::post('/manage/{waitingList}/promote')->uses('WaitingListPositionController@store')->name('manage.promote');
    Route::post('/manage/{waitingList}/demote')->uses('WaitingListPositionController@update')->name('manage.demote');
});
