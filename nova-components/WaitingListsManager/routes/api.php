<?php

use Illuminate\Support\Facades\Route;

Route::get('/accounts/{waitingList}', 'Vatsimuk\WaitingListsManager\WaitingListsManagerController@index');
