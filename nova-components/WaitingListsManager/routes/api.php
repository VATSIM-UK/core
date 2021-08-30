<?php

use Illuminate\Support\Facades\Route;

Route::get('/waitingLists/{waitingList}/available-places', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@getAvailablePlaces');

Route::get('/accounts/{waitingList}', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@index');
Route::post('/accounts/{waitingList}/remove', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@destroy');
Route::post('/accounts/{waitingList}/promote', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@promote');
Route::post('/accounts/{waitingList}/demote', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@demote');
Route::patch('/accounts/{waitingList}/defer', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@defer');
Route::get('/accounts/{waitingList}/active/index', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@activeIndex');
Route::patch('/accounts/{waitingList}/active', 'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@active');

Route::patch('/notes/{waitingListAccount}/create', 'Vatsimuk\WaitingListsManager\Http\WaitingListNoteController@create');

Route::patch('/flag/{waitingListAccountFlag}/toggle', 'Vatsimuk\WaitingListsManager\Http\WaitingListFlagController@toggle');

Route::post('/waitingLists/{waitingList}/position/{trainingPosition}/offer',
    'Vatsimuk\WaitingListsManager\Http\WaitingListsManagerController@offerTrainingPlace'
);
