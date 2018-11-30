<?php

use Illuminate\Support\Facades\Route;

Route::get('/accounts/{waitingList}', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@index');
Route::post('/accounts/{waitingList}/remove', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@destroy');
