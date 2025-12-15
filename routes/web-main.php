<?php

// Dashboard

Route::get('/dashboard')->uses('Mship\Management@getLanding')->name('landing');

// Authentication
Route::get('login')->uses('Auth\LoginController@login')->name('login');
Route::post('login')->uses('Auth\LoginController@login')->name('login.post');
Route::get('login-secondary')->uses('Auth\LoginController@showLoginForm')->middleware('auth:vatsim-sso')->name('auth-secondary');
Route::post('login-secondary')->uses('Auth\SecondaryLoginController@loginSecondary')->middleware('auth:vatsim-sso')->name('auth-secondary.post');
Route::post('logout')->uses('Auth\LogoutController')->name('logout');

Route::get('/staff')->uses('Site\StaffPageController@staff')->middleware('auth_full_group')->name('site.staff');

Route::view('banned-network', 'errors.banned-network')->name('banned.network');
Route::get('banned-local')->uses('Auth\LocalBanDisplayController')->name('banned.local');

// Password
Route::group([
    'as' => 'password.',
    'prefix' => 'password',
], function () {
    // Reset
    Route::group([
        'middleware' => 'auth:vatsim-sso',
    ], function () {
        Route::post('email')->uses('Auth\ForgotPasswordController@sendResetLinkEmail')->name('email');
        Route::get('reset/{token}')->uses('Auth\ResetPasswordController@showResetForm')->name('reset');
        Route::post('reset')->uses('Auth\ResetPasswordController@reset')->name('request');
    });

    // Change
    Route::group([
        'middleware' => 'auth_full_group',
    ], function () {
        Route::get('create')->uses('Auth\ChangePasswordController@showCreateForm')->name('create');
        Route::post('create')->uses('Auth\ChangePasswordController@create');
        Route::get('change')->uses('Auth\ChangePasswordController@showChangeForm')->name('change');
        Route::post('change')->uses('Auth\ChangePasswordController@change');
        Route::get('delete', 'Auth\ChangePasswordController@showDeleteForm')->name('delete');
        Route::post('delete')->uses('Auth\ChangePasswordController@delete');
    });
});

// Members
Route::group([
    'prefix' => 'mship',
    'as' => 'mship.',
    'namespace' => 'Mship',
    'middleware' => 'auth_full_group',
], function () {
    // Manage
    Route::group([
        'as' => 'manage.',
        'prefix' => 'manage',
    ], function () {
        Route::get('dashboard')->uses('Management@getDashboard')->name('dashboard');
        Route::get('cert/update')->uses('Management@requestCertCheck')->name('cert.update');
        Route::get('email/verify/{code}')->uses('Management@getVerifyEmail')->name('email.verify');
        Route::get('email/add')->uses('Management@getEmailAdd')->name('email.add');
        Route::post('email/add')->uses('Management@postEmailAdd')->name('email.add.post');
        Route::get('email/delete/{email}')->uses('Management@getEmailDelete')->name('email.delete');
        Route::post('email/delete/{email}')->uses('Management@postEmailDelete')->name('email.delete.post');
        Route::get('email/assignments')->uses('Management@getEmailAssignments')->name('email.assignments');
        Route::post('email/assignments')->uses('Management@postEmailAssignments')->name('email.assignments.post');
    });

    // Feedback
    Route::group([
        'as' => 'feedback.',
        'prefix' => 'feedback',
    ], function () {
        Route::get('new')->uses('Feedback@getFeedbackFormSelect')->name('new');
        Route::post('new')->uses('Feedback@postFeedbackFormSelect')->name('new.post');
        Route::get('new/{form}')->uses('Feedback@getFeedback')->name('new.form');
        Route::post('new/{form}')->uses('Feedback@postFeedback')->name('new.form.post');
        Route::get('view')->uses('Feedback\ViewFeedbackController@show')->name('view');
    });

    // Waiting Lists
    Route::group([
        'as' => 'waiting-lists.',
        'prefix' => 'waiting-lists',
    ], function () {
        Route::get('')->uses('WaitingLists@index')->name('index');
        Route::post('self-enrol/{waitingList}')->uses('WaitingLists@selfEnrol')->name('self-enrol');
    });

    // Other
    Route::group([
    ], function () {

        Route::get('notification/list')->uses('Notification@getList')->name('notification.list');
        Route::post('notification/acknowledge/{sysNotification}')->uses('Notification@postAcknowledge')->name('notification.acknowledge');
    });
});

// Waiting Lists - Retention (No authentication required)
Route::get('mship/waiting-lists/retention')->uses('Mship\WaitingLists@getRetentionWithToken')->name('mship.waiting-lists.retention.token');

Route::get('atcfb', function () {
    return redirect()
        ->route('mship.feedback.new.form', [
            'form' => 'atc',
            'cid' => request()->get('cid'),
        ]);
})->name('mship.feedback.redirect.atc');

// TeamSpeak
Route::group([
    'prefix' => 'mship/manage/teamspeak',
    'namespace' => 'TeamSpeak',
    'middleware' => 'auth_full_group',
], function () {
    Route::model('tsreg', App\Models\TeamSpeak\Registration::class);
    Route::get('new', ['as' => 'teamspeak.new', 'uses' => 'Registration@getNew']);
    Route::get('success', ['as' => 'teamspeak.success', 'uses' => 'Registration@getConfirmed']);
    Route::get('{mshipRegistration}/delete', ['as' => 'teamspeak.delete', 'uses' => 'Registration@getDelete']);
    Route::post('{mshipRegistration}/status', ['as' => 'teamspeak.status', 'uses' => 'Registration@postStatus']);
});

// Discord
Route::group([
    'as' => 'discord.',
    'prefix' => 'discord',
    'namespace' => 'Discord',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('/')->uses('Registration@show')->name('show');
    Route::get('/create')->uses('Registration@create')->name('create');
    Route::get('/store')->uses('Registration@store')->name('store');
    Route::get('/destroy')->uses('Registration@destroy')->name('destroy');
});

// UKCP
Route::group([
    'as' => 'ukcp.',
    'prefix' => 'ukcp',
    'namespace' => 'UKCP',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('/')->uses('Token@show')->name('guide');
    Route::get('/token/invalidate')->uses('Token@invalidate')->name('token.invalidate');
});

// Controllers
Route::group([
    'as' => 'controllers.',
    'prefix' => 'controllers/',
    'namespace' => 'Atc',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('endorsements/gatwick')->uses('EndorsementController@getGatwickGroundIndex')->name('endorsements.gatwick_ground');
    Route::get('endorsements/heathrow-s1')->uses('EndorsementController@getHeathrowGroundS1Index')->name('endorsements.heathrow_ground_s1');
});

// Network data
Route::group([
    'as' => 'networkdata.',
    'prefix' => 'network-data',
    'namespace' => 'NetworkData',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('dashboard')->uses('MainController@getDashboard')->name('dashboard');
});

Route::group([
    'as' => 'visiting.',
    'prefix' => 'visit-transfer',
    'namespace' => 'VisitTransfer\Site',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('/', ['as' => 'landing', 'uses' => 'Dashboard@getDashboard']);

    // Application
    Route::group([
        'as' => 'application.',
        'prefix' => 'application',
    ], function () {
        // Start
        Route::get('start/{type}/{team}')->uses('Application@getStart')->name('start')->where('type', "\d+");
        Route::post('start/{type}/{team}')->uses('Application@postStart')->name('start.post')->where('type', "\d+");

        // Continue
        Route::group([
            'prefix' => '{applicationByPublicId}',
        ], function () {
            Route::get('/')->uses('Application@getView')->name('view');
            Route::get('continue')->uses('Application@getContinue')->name('continue');
            Route::get('facility')->uses('Application@getFacility')->name('facility');
            Route::post('facility')->uses('Application@postFacility')->name('facility.post');
            Route::post('facility/manual')->uses('Application@postManualFacility')->name('facility.manual.post');
            Route::get('statement')->uses('Application@getStatement')->name('statement');
            Route::post('statement')->uses('Application@postStatement')->name('statement.post');
            Route::get('referees')->uses('Application@getReferees')->name('referees');
            Route::post('referees')->uses('Application@postReferees')->name('referees.post');
            Route::post('referees/{reference}/delete')->uses('Application@postRefereeDelete')->name('referees.delete.post');
            Route::get('submit')->uses('Application@getSubmit')->name('submit');
            Route::post('submit')->uses('Application@postSubmit')->name('submit.post');
            Route::get('withdraw')->uses('Application@getWithdraw')->name('withdraw');
            Route::post('withdraw')->uses('Application@postWithdraw')->name('withdraw.post');
        });
    });

    // References
    Route::group([
        'as' => 'reference.',
        'prefix' => 'reference',
    ], function () {
        Route::get('complete/{token}')->uses('Reference@getComplete')->name('complete');
        Route::post('complete/{token}')->uses('Reference@postComplete')->name('complete.post');
        Route::post('complete/{token}/cancel')->uses('Reference@postCancel')->name('complete.cancel');
    });
});
