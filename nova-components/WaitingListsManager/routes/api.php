<?php

use Illuminate\Support\Facades\Route;

Route::get('/accounts/{waitingList}', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@index');
Route::post('/accounts/{waitingList}/remove', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@destroy');
Route::post('/accounts/{waitingList}/promote', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@promote');
Route::post('/accounts/{waitingList}/demote', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@demote');
Route::patch('/accounts/{waitingList}/defer', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@defer');
Route::patch('/accounts/{waitingList}/active', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@active');
