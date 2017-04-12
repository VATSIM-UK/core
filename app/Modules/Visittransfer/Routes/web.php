<?php

Route::get('/visiting-transferring', function () {
    return Redirect::route('visiting.landing');
});

Route::group([
    'as' => 'visiting.admin.',
    'prefix' => 'adm/visit-transfer',
    'namespace' => 'Admin',
    'domain' => config('app.url'),
    'middleware' => ['auth.admin'],
], function () {
    Route::get('/', [
        'as' => 'dashboard',
        'uses' => 'Dashboard@getDashboard',
    ]);

    Route::get('/facility', [
        'as' => 'facility',
        'uses' => 'Facility@getList',
    ]);

    Route::get('/facility/create', [
        'as' => 'facility.create',
        'uses' => 'Facility@getCreate',
    ]);

    Route::post('/facility/create', [
        'as' => 'facility.create.post',
        'uses' => 'Facility@postCreate',
    ]);

    Route::get('/facility/{facility}/update', [
        'as' => 'facility.update',
        'uses' => 'Facility@getUpdate',
    ])->where('facility', "\d+");

    Route::post('/facility/{facility}/update', [
        'as' => 'facility.update.post',
        'uses' => 'Facility@postUpdate',
    ])->where('facility', "\d+");

    Route::get('/reference/{reference}', [
        'as' => 'reference.view',
        'uses' => 'Reference@getView',
    ])->where('reference', "\d+");

    Route::post('/reference/{reference}/reject', [
        'as' => 'reference.reject.post',
        'uses' => 'Reference@postReject',
    ])->where('reference', "\d+");

    Route::post('/reference/{reference}/accept', [
        'as' => 'reference.accept.post',
        'uses' => 'Reference@postAccept',
    ])->where('reference', "\d+");

    Route::get('/reference/{scope?}', [
        'as' => 'reference.list',
        'uses' => 'Reference@getList',
    ])->where('scope', '[a-zA-Z-]+');

    Route::get('/application/{application}', [
        'as' => 'application.view',
        'uses' => 'Application@getView',
    ])->where('application', "\d+");

    Route::post('/application/{application}/check/met', [
        'as' => 'application.check.met.post',
        'uses' => 'Application@postCheckMet',
    ]);

    Route::post('/application/{application}/check/not-met', [
        'as' => 'application.check.notmet.post',
        'uses' => 'Application@postCheckNotMet',
    ]);

    Route::post('/application/{application}/setting/toggle', [
        'as' => 'application.setting.toggle.post',
        'uses' => 'Application@postSettingToggle',
    ]);

    Route::post('/application/{application}/accept', [
        'as' => 'application.accept.post',
        'uses' => 'Application@postAccept',
    ])->where('application', "\d+");

    Route::post('/application/{application}/reject', [
        'as' => 'application.reject.post',
        'uses' => 'Application@postReject',
    ])->where('application', "\d+");

    Route::get('/application/{scope?}', [
        'as' => 'application.list',
        'uses' => 'Application@getList',
    ])->where('scope', "\w+");
});

Route::group([
    'as' => 'visiting.',
    'prefix' => 'visit-transfer',
    'namespace' => 'Site',
    'domain' => config('app.url'),
    'middleware' => ['auth_full_group'],
], function () {
    Route::get('/', ['as' => 'landing', 'uses' => 'Dashboard@getDashboard']);

    Route::group(['as' => 'application.', 'prefix' => 'application'], function () {
        Route::get('', function () {
            return Redirect::route('visiting.landing');
        });

        Route::get('/start/{type}/{team}', [
            'as' => 'start',
            'uses' => 'Application@getStart',
        ])->where('type', "\d+");

        Route::post('/start/{type}/{team}', [
            'as' => 'start.post',
            'uses' => 'Application@postStart',
        ])->where('type', "\d+");

        Route::group(['prefix' => '/{applicationByPublicId}'], function () {
            Route::get('/continue', [
                'as' => 'continue',
                'uses' => 'Application@getContinue',
            ]);

            Route::get('/facility', [
                'as' => 'facility',
                'uses' => 'Application@getFacility',
            ]);

            Route::post('/facility', [
                'as' => 'facility.post',
                'uses' => 'Application@postFacility',
            ]);

            Route::get('/statement', [
                'as' => 'statement',
                'uses' => 'Application@getStatement',
            ]);

            Route::post('/statement', [
                'as' => 'statement.post',
                'uses' => 'Application@postStatement',
            ]);

            Route::get('/referees', [
                'as' => 'referees',
                'uses' => 'Application@getReferees',
            ]);

            Route::post('/referees', [
                'as' => 'referees.post',
                'uses' => 'Application@postReferees',
            ]);

            Route::post('/referees/{reference}/delete', [
                'as' => 'referees.delete.post',
                'uses' => 'Application@postRefereeDelete',
            ]);

            Route::get('/submit', [
                'as' => 'submit',
                'uses' => 'Application@getSubmit',
            ]);

            Route::post('/submit', [
                'as' => 'submit.post',
                'uses' => 'Application@postSubmit',
            ]);

            Route::get('/withdraw', [
                'as' => 'withdraw',
                'uses' => 'Application@getWithdraw',
            ]);

            Route::post('/withdraw', [
                'as' => 'withdraw.post',
                'uses' => 'Application@postWithdraw',
            ]);

            Route::get('', [
                'as' => 'view',
                'uses' => 'Application@getView',
            ]);
        });
    });

    Route::group(['as' => 'reference.', 'prefix' => 'reference'], function () {
        Route::get('/complete/{token}', [
            'as' => 'complete',
            'uses' => 'Reference@getComplete',
        ]);

        Route::post('/complete/{token}', [
            'as' => 'complete.post',
            'uses' => 'Reference@postComplete',
        ]);
    });
});
