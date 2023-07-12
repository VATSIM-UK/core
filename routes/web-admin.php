<?php

// Admin
Route::group([
    'prefix' => 'adm',
    'namespace' => 'Adm',
    'middleware' => ['auth_full_group', 'admin'],
    'as' => 'adm.',
], function () {
    // Main
    Route::get('/')->uses('Dashboard@index')->name('index');
    Route::any('/search/{q?}')->uses('Dashboard@anySearch')->name('search');

    // smartCARS
    Route::group([
        'prefix' => 'smartcars',
        'namespace' => 'Smartcars\Resources',
        'as' => 'smartcars.',
    ], function () {
        Route::resource('configure/aircraft', 'AircraftController')->except('show');
        Route::resource('configure/airports', 'AirportController')->except('show');
        Route::resource('configure/exercises', 'ExerciseController')->except('show');
        Route::resource('exercises.resources', 'ExerciseResourceController')->except('show');
        Route::resource('flights', 'FlightController')->only('index', 'edit', 'update');
    });

    // ATC
    Route::group([
        'prefix' => 'atc',
        'namespace' => 'Atc',
        'as' => 'atc.',
    ], function () {
        Route::get('endorsement')->uses('Endorsement@getIndex')->name('endorsement.index');
    });

    // Members
    Route::group([
        'prefix' => 'mship',
        'as' => 'mship.',
        'namespace' => 'Mship',
    ], function () {
        // Account
        Route::group([
            'prefix' => 'account/',
            'as' => 'account.',
        ], function () {
            Route::get('/account/{scope?}')->where(['scope' => '\w+'])->uses('Account@getIndex')->name('index');
            Route::get('{mshipAccount}/sync')->where(['mshipAccount' => '\d+'])->uses('Account\Settings@sync')->name('sync');
            Route::get('{mshipAccount}/{tab?}/{tabid?}')->where(['mshipAccount' => '\d+'])->uses('Account@getDetail')->name('details');
            Route::post('{mshipAccount}/roles/attach')->where(['mshipAccount' => '\d+'])->uses('Account\Roles@postRoleAttach')->name('role.attach');
            Route::get('{mshipAccount}/roles/{mshipRole}/detach')->where(['mshipAccount' => '\d+'])->uses('Account\Roles@getRoleDetach')->name('role.detach');
            Route::post('{mshipAccount}/ban/add')->where(['mshipAccount' => '\d+'])->uses('Account\Bans@postBanAdd')->name('ban.add');
            Route::post('{mshipAccount}/note/create')->where(['mshipAccount' => '\d+'])->uses('Account\Settings@postNoteCreate')->name('note.create');
            Route::post('{mshipAccount}/note/filter')->where(['mshipAccount' => '\d+'])->uses('Account\Settings@postNoteFilter')->name('note.filter');
            Route::post('{mshipAccount}/impersonate')->where(['mshipAccount' => '\d+'])->uses('Account\Settings@postImpersonate')->name('impersonate');
        });

        // Bans
        Route::group([
            'prefix' => 'ban',
            'as' => 'ban.',
        ], function () {
            Route::get('/')->uses('Account\Bans@getBans')->name('index');
            Route::get('/{ban}/repeal')->where(['ban' => '\d+'])->uses('Account\Bans@getBanRepeal')->name('repeal');
            Route::post('/{ban}/repeal')->where(['ban' => '\d+'])->uses('Account\Bans@postBanRepeal')->name('repeal.post');
            Route::get('/{ban}/comment')->where(['ban' => '\d+'])->uses('Account\Bans@getBanComment')->name('comment');
            Route::post('/{ban}/comment')->where(['ban' => '\d+'])->uses('Account\Bans@postBanComment')->name('comment.post');
            Route::get('/{ban}/modify')->where(['ban' => '\d+'])->uses('Account\Bans@getBanModify')->name('modify');
            Route::post('/{ban}/modify')->where(['ban' => '\d+'])->uses('Account\Bans@postBanModify')->name('modify.post');
        });

        // Roles
        Route::group([
            'prefix' => 'role',
            'as' => 'role.',
        ], function () {
            Route::get('/')->uses('Role@getIndex')->name('index');
            Route::get('/create')->uses('Role@getCreate')->name('create');
            Route::post('/create')->uses('Role@postCreate')->name('create.post');
            Route::get('/{mshipRole}/update')->uses('Role@getUpdate')->name('update');
            Route::post('/{mshipRole}/update')->uses('Role@postUpdate')->name('update.post');
            Route::any('/{mshipRole}/delete')->uses('Role@anyDelete')->name('delete');
        });

        // Permissions
        Route::group([
            'prefix' => 'permission',
            'as' => 'permission.',
        ], function () {
            Route::get('/')->uses('Permission@getIndex')->name('index');
            Route::get('/create')->uses('Permission@getCreate')->name('create');
            Route::post('/create')->uses('Permission@postCreate')->name('create.post');
            Route::get('/{mshipPermission}/update')->uses('Permission@getUpdate')->name('update');
            Route::post('/{mshipPermission}/update')->uses('Permission@postUpdate')->name('update.post');
            Route::any('/{mshipPermission}/delete')->uses('Permission@anyDelete')->name('delete');
        });
    });

    // Visiting/Transferring
    Route::group([
        'as' => 'visiting.',
        'prefix' => 'visit-transfer',
        'namespace' => 'VisitTransfer',
    ], function () {
        Route::get('/')->uses('Dashboard@getDashboard')->name('dashboard');
        Route::get('/facility')->uses('Facility@getList')->name('facility');
        Route::get('/facility/create')->uses('Facility@getCreate')->name('facility.create');
        Route::post('/facility/create')->uses('Facility@postCreate')->name('facility.create.post');
        Route::get('/facility/{facility}/update')->where('facility', "\d+")->uses('Facility@getUpdate')->name('facility.update');
        Route::post('/facility/{facility}/update')->where('facility', "\d+")->uses('Facility@postUpdate')->name('facility.update.post');
        Route::get('/reference/{reference}')->where('reference', "\d+")->uses('Reference@getView')->name('reference.view');
        Route::post('/reference/{reference}/reject')->where('reference', "\d+")->uses('Reference@postReject')->name('reference.reject.post');
        Route::post('/reference/{reference}/accept')->where('reference', "\d+")->uses('Reference@postAccept')->name('reference.accept.post');
        Route::get('/reference/{scope?}')->where('scope', '[a-zA-Z-]+')->uses('Reference@getList')->name('reference.list');
        Route::get('/application/{application}')->where('application', "\d+")->uses('Application@getView')->name('application.view');
        Route::post('/application/{application}/check/met')->uses('Application@postCheckMet')->name('application.check.met.post');
        Route::post('/application/{application}/check/not-met')->uses('Application@postCheckNotMet')->name('application.check.notmet.post');
        Route::post('/application/{application}/setting/toggle')->uses('Application@postSettingToggle')->name('application.setting.toggle.post');
        Route::post('/application/{application}/accept')->where('application', "\d+")->uses('Application@postAccept')->name('application.accept.post');
        Route::post('/application/{application}/reject')->where('application', "\d+")->uses('Application@postReject')->name('application.reject.post');
        Route::post('/application/{application}/complete')->where('application', "\d+")->uses('Application@postComplete')->name('application.complete.post');
        Route::post('/application/{application}/cancel')->where('application', "\d+")->uses('Application@postCancel')->name('application.cancel.post');
        Route::get('/application/{scope?}')->where('scope', "\w+")->uses('Application@getList')->name('application.list');
        Route::get('/hours/')->uses('VisitorStatsController@create')->name('hours.create');
        Route::get('/hours/search')->uses('VisitorStatsController@index')->name('hours.search');
    });
});
