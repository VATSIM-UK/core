<?php

// Admin
Route::group([
    'prefix' => 'adm',
    'namespace' => 'Adm',
    'middleware' => ['auth_full_group', 'admin'],
    'as' => 'adm.',
], function () {

    // Main
    Route::get('/')->uses('Dashboard@getIndex');
    Route::get('/dashboard')->uses('Dashboard@getIndex')->name('dashboard');
    Route::any('/search/{q?}')->uses('Dashboard@anySearch')->name('search');

    // System
    Route::group([
        'as' => 'sys.',
        'prefix' => 'system',
        'namespace' => 'Sys',
    ], function () {
        Route::get('/activity')->uses('Activity@getIndex')->name('activity.list');

        Route::get('/jobs/failed')->uses('Jobs@getFailed')->name('jobs.failed');
        Route::post('/jobs/failed/{id}/retry')->uses('Jobs@postFailed')->name('jobs.failed.retry');
        Route::delete('/jobs/failed/{id}/delete')->uses('Jobs@deleteFailed')->name('jobs.failed.delete');
    });

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

    // Operations
    Route::group([
        'prefix' => 'ops',
        'namespace' => 'Operations',
        'as' => 'ops.',
    ], function () {
        Route::get('qstats')->uses('QuarterlyStats@get')->name('qstats.index');
        Route::post('qstats')->uses('QuarterlyStats@generate')->name('qstats.generate');
    });

    // Network Data
    Route::group([
        'as' => 'networkdata.',
        'namespace' => 'NetworkData',
        'prefix' => 'network-data',
        'middleware' => 'auth_full_group',
    ], function () {
        Route::get('/')->uses('Dashboard@getDashboard')->name('dashboard');
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
            Route::get('{mshipAccount}/{tab?}/{tabid?}')->where(['mshipAccount' => '\d+'])->uses('Account@getDetail')->name('details');
            Route::post('{mshipAccount}/roles/attach')->where(['mshipAccount' => '\d+'])->uses('Account@postRoleAttach')->name('role.attach');
            Route::get('{mshipAccount}/roles/{mshipRole}/detach')->where(['mshipAccount' => '\d+'])->uses('Account@getRoleDetach')->name('role.detach');
            Route::post('{mshipAccount}/ban/add')->where(['mshipAccount' => '\d+'])->uses('Account@postBanAdd')->name('ban.add');
            Route::post('{mshipAccount}/note/create')->where(['mshipAccount' => '\d+'])->uses('Account@postNoteCreate')->name('note.create');
            Route::post('{mshipAccount}/note/filter')->where(['mshipAccount' => '\d+'])->uses('Account@postNoteFilter')->name('note.filter');
            Route::post('{mshipAccount}/security/enable')->where(['mshipAccount' => '\d+'])->uses('Account@postSecurityEnable')->name('security.enable');
            Route::post('{mshipAccount}/security/reset')->where(['mshipAccount' => '\d+'])->uses('Account@postSecurityReset')->name('security.reset');
            Route::post('{mshipAccount}/security/change')->where(['mshipAccount' => '\d+'])->uses('Account@postSecurityChange')->name('security.change');
            Route::post('{mshipAccount}/impersonate')->where(['mshipAccount' => '\d+'])->uses('Account@postImpersonate')->name('impersonate');
        });

        // Bans
        Route::group([
            'prefix' => 'ban',
            'as' => 'ban.',
        ], function () {
            Route::get('/')->uses('Account@getBans')->name('index');
            Route::get('/{ban}/repeal')->where(['ban' => '\d+'])->uses('Account@getBanRepeal')->name('repeal');
            Route::post('/{ban}/repeal')->where(['ban' => '\d+'])->uses('Account@postBanRepeal')->name('repeal.post');
            Route::get('/{ban}/comment')->where(['ban' => '\d+'])->uses('Account@getBanComment')->name('comment');
            Route::post('/{ban}/comment')->where(['ban' => '\d+'])->uses('Account@postBanComment')->name('comment.post');
            Route::get('/{ban}/modify')->where(['ban' => '\d+'])->uses('Account@getBanModify')->name('modify');
            Route::post('/{ban}/modify')->where(['ban' => '\d+'])->uses('Account@postBanModify')->name('modify.post');
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

        // Notes
        Route::group([
            'prefix' => 'note/type',
            'as' => 'note.type.',
        ], function () {
            Route::get('')->uses('Note@getTypeIndex')->name('index');
            Route::get('/create')->uses('Note@getTypeCreate')->name('create');
            Route::post('/create')->uses('Note@postTypeCreate')->name('create.post');
            Route::get('/{mshipNoteType}/update')->uses('Note@getTypeUpdate')->name('update');
            Route::post('/{mshipNoteType}/update')->uses('Note@postTypeUpdate')->name('update.post');
            Route::any('/{mshipNoteType}/delete')->uses('Note@anyTypeDelete')->name('delete');
        });

        // Feedback
        Route::group([
            'prefix' => 'feedback',
            'as' => 'feedback.',
        ], function () {
            Route::get('/')->uses('Feedback@getListForms')->name('forms');
            Route::get('new')->uses('Feedback@getNewForm')->name('new');
            Route::post('new')->uses('Feedback@postNewForm')->name('new.create');
            Route::get('configure/{form}')->uses('Feedback@getConfigure')->name('config');
            Route::post('configure/{form}')->uses('Feedback@postConfigure')->name('config.save');
            Route::get('configure/{form}/toggle')->uses('Feedback@getEnableDisableForm')->name('config.toggle');
            Route::get('configure/{form}/visibility')->uses('Feedback@getFormVisibility')->name('config.visibility');
            Route::get('list')->uses('Feedback@getAllFeedback')->name('all');
            Route::get('list/{slug}')->uses('Feedback@getFormFeedback')->name('form');
            Route::get('list/{slug}/export')->uses('Feedback@getFormFeedbackExport')->name('form.export');
            Route::post('list/{slug}/export')->uses('Feedback@postFormFeedbackExport')->name('form.export.post');
            Route::get('view/{feedback}')->uses('Feedback@getViewFeedback')->name('view');
            Route::post('view/{feedback}/action')->uses('Feedback@postActioned')->name('action');
            Route::get('view/{feedback}/unaction')->uses('Feedback@getUnActioned')->name('unaction');
            Route::post('view/{feedback}/send')->uses('Feedback\FeedbackSendController@store')->name('send');
        });

        // Other
        Route::get('staff')->uses('Staff@getIndex')->name('staff.index');
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
        Route::get('/application/{scope?}')->where('scope', "\w+")->uses('Application@getList')->name('application.list');
        Route::get('/hours/')->uses('VisitorStatsController@create')->name('hours.create');
        Route::get('/hours/search')->uses('VisitorStatsController@index')->name('hours.search');
    });
});
