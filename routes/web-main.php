<?php

// Dashboard
Route::get('/dashboard')->uses('Mship\Management@getLanding')->name('dashboard');

// Authentication
Route::get('login')->uses('Auth\LoginController@getLogin');
Route::post('login')->uses('Auth\LoginController@loginMain')->name('login');
Route::get('login-secondary')->uses('Auth\LoginController@showLoginForm')->middleware('auth:vatsim-sso')->name('auth-secondary');
Route::post('login-secondary')->uses('Auth\LoginController@loginSecondary')->middleware('auth:vatsim-sso')->name('auth-secondary.post');
Route::get('login-vatsim')->uses('Auth\LoginController@vatsimSsoReturn')->name('auth-vatsim-sso');
Route::post('logout')->uses('Auth\LoginController@logout')->name('logout');

// Password
Route::group([
    'as'     => 'password.',
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

// Webhooks
Route::group([
    'as'        => 'webhook.',
    'prefix'    => 'webhook',
    'namespace' => 'Webhook',
], function () {
    Route::get('dropbox')->uses('Dropbox@getDropbox')->name('dropbox');
    Route::post('dropbox')->uses('Dropbox@postDropbox');

    Route::any('slack')->uses('Slack@anyRouter')->name('slack');

    Route::post('mailgun')->uses('Mailgun@event')->middleware('auth.basic.once');
    Route::post('sendgrid')->uses('SendGrid@events')->middleware('auth.basic.once');
});

// Members
Route::group([
    'prefix'     => 'mship',
    'as'         => 'mship.',
    'namespace'  => 'Mship',
    'middleware' => 'auth_full_group',
], function () {

    // Manage
    Route::group([
        'as'     => 'manage.',
        'prefix' => 'manage',
    ], function () {
        Route::get('dashboard')->uses('Management@getDashboard')->name('dashboard');
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
        'as'     => 'feedback.',
        'prefix' => 'feedback',
    ], function () {
        Route::get('new')->uses('Feedback@getFeedbackFormSelect')->name('new');
        Route::post('new')->uses('Feedback@postFeedbackFormSelect')->name('new.post');
        Route::get('new/{form}')->uses('Feedback@getFeedback')->name('new.form');
        Route::post('new/{form}')->uses('Feedback@postFeedback')->name('new.form.post');
        Route::get('users/search/{name}')->uses('Feedback@getUserSearch')->name('usersearch');
        Route::get('view')->uses('Feedback\ViewFeedbackController@show')->name('view');
    });

    // Other
    Route::group([
    ], function () {
        Route::post('auth/invisibility')->uses('Management@postInvisibility')->name('auth.invisibility');

        Route::get('notification/list')->uses('Notification@getList')->name('notification.list');
        Route::post('notification/acknowledge/{sysNotification}')->uses('Notification@postAcknowledge')->name('notification.acknowledge');

        // Route::get('/email')->uses('Email@getEmail')->name('mship.email');
        // Route::post('/email')->uses('Email@postEmail')->name('mship.email.post');
        // Route::get('/email/recipient-search')->uses('Email@getRecipientSearch')->name('mship.email.recipient-search');
    });
});

// TeamSpeak
Route::group([
    'prefix'     => 'mship/manage/teamspeak',
    'namespace'  => 'TeamSpeak',
    'middleware' => 'auth_full_group',
], function () {
    Route::model('tsreg', App\Models\TeamSpeak\Registration::class);
    Route::get('new', ['as' => 'teamspeak.new', 'uses' => 'Registration@getNew']);
    Route::get('success', ['as' => 'teamspeak.success', 'uses' => 'Registration@getConfirmed']);
    Route::get('{mshipRegistration}/delete', ['as' => 'teamspeak.delete', 'uses' => 'Registration@getDelete']);
    Route::post('{mshipRegistration}/status', ['as' => 'teamspeak.status', 'uses' => 'Registration@postStatus']);
});

Route::group(['prefix' => 'mship/manage/slack', 'namespace' => 'Slack', 'middleware' => ['auth_full_group']], function () {
    Route::model('slackToken', App\Models\Sys\Token::class);
    Route::get('/new', ['as' => 'slack.new', 'uses' => 'Registration@getNew']);
    Route::get('/success', ['as' => 'slack.success', 'uses' => 'Registration@getConfirmed']);
    Route::post('/{slackToken}/status', ['as' => 'slack.status', 'uses' => 'Registration@postStatus']);
});

// UKCP
Route::group([
    'as'         => 'ukcp.',
    'prefix'     => 'ukcp',
    'namespace'  => 'UKCP',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('/')->uses('Token@show')->name('guide');
    Route::get('/token')->uses('Token@create')->name('token.create');
    Route::get('token/{id}/destroy')->uses('Token@destroy')->name('token.destroy');
    Route::get('token/{id}/download')->uses('Token@download')->name('token.download');
});

// Community
Route::group([
    'as'         => 'community.membership.',
    'prefix'     => 'community/membership',
    'namespace'  => 'Community',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('deploy')->uses('Membership@getDeploy')->name('deploy');
    Route::post('deploy/{default?}')->uses('Membership@postDeploy')->name('deploy.post')
        ->where('default', '[default|true]');
});

// Controllers
Route::group([
    'as'         => 'controllers.',
    'prefix'     => 'controllers/',
    'namespace'  => 'Atc',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('endorsements/gatwick')->uses('EndorsementController@getGatwickGroundIndex')->name('endorsements.gatwick_ground');
});

// Network data
Route::group([
    'as'         => 'networkdata.',
    'prefix'     => 'network-data',
    'namespace'  => 'NetworkData',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('dashboard')->uses('MainController@getDashboard')->name('dashboard');
    Route::get('online')->uses('Online@getOnline')->name('online');
});

Route::group([
    'as'         => 'visiting.',
    'prefix'     => 'visit-transfer',
    'namespace'  => 'VisitTransfer\Site',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('/', ['as' => 'landing', 'uses' => 'Dashboard@getDashboard']);

    // Application
    Route::group([
        'as'     => 'application.',
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
        'as'     => 'reference.',
        'prefix' => 'reference',
    ], function () {
        Route::get('complete/{token}')->uses('Reference@getComplete')->name('complete');
        Route::post('complete/{token}')->uses('Reference@postComplete')->name('complete.post');
        Route::post('complete/{token}/cancel')->uses('Reference@postCancel')->name('complete.cancel');
    });
});

// SmartCARS
Route::any('frame.php', 'Smartcars\Api\Router@routeRequest');

Route::group([
    'as'         => 'fte.',
    'prefix'     => 'fte',
    'namespace'  => 'Smartcars',
    'middleware' => 'auth_full_group',
], function () {
    Route::get('dashboard')->uses('SmartcarsController@getDashboard')->name('dashboard');
    Route::get('map')->uses('SmartcarsController@getMap')->name('map');
    Route::get('exercises/{exercise?}')->uses('SmartcarsController@getExercise')->name('exercises');
    Route::post('exercises/{exercise}/book')->uses('SmartcarsController@bookExercise')->name('exercise.book');
    Route::post('exercises/{exercise}/cancel')->uses('SmartcarsController@cancelExercise')->name('exercise.cancel');
    Route::get('history/{pirep?}')->uses('SmartcarsController@getHistory')->name('history');
    Route::get('guide')->uses('SmartcarsController@getGuide')->name('guide');
});
