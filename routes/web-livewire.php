<?php

use App\Livewire\RetentionChecks\Fail;
use App\Livewire\RetentionChecks\Success;
use App\Livewire\Roster\Index;
use App\Livewire\Roster\Renew;
use App\Livewire\Roster\Search;
use App\Livewire\Roster\Show;

Route::group([
    'as' => 'site.roster.',
    'prefix' => 'roster',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('/', Index::class)->name('index');
    Route::get('/renew', Renew::class)->name('renew');
    Route::get('/search', Search::class)->name('search');
    Route::get('/{account}', Show::class)->name('show');
});

Route::get('mship/waiting-lists/retention/success', Success::class)->name('mship.waiting-lists.retention.success');
Route::get('mship/waiting-lists/retention/fail', Fail::class)->name('mship.waiting-lists.retention.fail');
