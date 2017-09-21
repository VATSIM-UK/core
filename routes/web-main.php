<?php

// Index
Route::get('/', 'Mship\Management@getLanding')->name('default');

// Authentication
Route::get('login', 'Auth\LoginController@getLogin');
Route::post('login', 'Auth\LoginController@loginMain')->name('login');
Route::get('login-secondary', 'Auth\LoginController@showLoginForm')->name('auth-secondary')->middleware('auth:vatsim-sso');
Route::post('login-secondary', 'Auth\LoginController@loginSecondary')->name('auth-secondary.post')->middleware('auth:vatsim-sso');
Route::get('login-vatsim', 'Auth\LoginController@vatsimSsoReturn')->name('auth-vatsim-sso');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Password Reset
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.request');

// Password Change
Route::group(['middleware' => ['auth_full_group']], function () {
    Route::get('password/create', 'Auth\ChangePasswordController@showCreateForm')->name('password.create');
    Route::post('password/create', 'Auth\ChangePasswordController@create');
    Route::get('password/change', 'Auth\ChangePasswordController@showChangeForm')->name('password.change');
    Route::post('password/change', 'Auth\ChangePasswordController@change');
    Route::get('password/delete', 'Auth\ChangePasswordController@showDeleteForm')->name('password.delete');
    Route::post('password/delete', 'Auth\ChangePasswordController@delete');
});

// Webhooks
Route::group(['prefix' => 'webhook', 'namespace' => 'Webhook'], function () {
    Route::get('dropbox', 'Dropbox@getDropbox')->name('webhook.dropbox');
    Route::post('dropbox', 'Dropbox@postDropbox');

    Route::any('slack', 'Slack@anyRouter')->name('webhook.slack');

    Route::post('mailgun', 'Mailgun@event')->middleware('auth.basic.once');
    Route::post('sendgrid', 'SendGrid@events')->middleware('auth.basic.once');
});

// Members

Route::group(['prefix' => 'mship', 'namespace' => 'Mship'], function () {
    // Guest access
    Route::group(['prefix' => 'auth'], function () {
        Route::get('login-alternative', ['as' => 'mship.auth.loginAlternative', 'uses' => 'Authentication@getLoginAlternative']);
        Route::post('login-alternative', ['as' => 'mship.auth.loginAlternative.post', 'uses' => 'Authentication@postLoginAlternative']);

        Route::group(['middleware' => ['auth_full_group']], function () {
            Route::post('invisibility', ['as' => 'mship.auth.invisibility', 'uses' => 'Management@postInvisibility']);
        });
    });

    Route::group(['middleware' => 'auth_full_group', 'prefix' => 'notification'], function () {
        Route::get('/list', ['as' => 'mship.notification.list', 'uses' => 'Notification@getList']);
        Route::post('/acknowledge/{sysNotification}', ['as' => 'mship.notification.acknowledge', 'uses' => 'Notification@postAcknowledge']);
    });

    Route::group(['prefix' => 'manage', 'middleware' => ['auth_full_group']], function () {
        Route::get('dashboard', ['as' => 'mship.manage.dashboard', 'uses' => 'Management@getDashboard']);
        Route::get('email/verify/{code}', ['as' => 'mship.manage.email.verify', 'uses' => 'Management@getVerifyEmail']);
        Route::get('email/add', ['as' => 'mship.manage.email.add', 'uses' => 'Management@getEmailAdd']);
        Route::post('email/add', ['as' => 'mship.manage.email.add.post', 'uses' => 'Management@postEmailAdd']);
        Route::get('email/delete/{email}', ['as' => 'mship.manage.email.delete', 'uses' => 'Management@getEmailDelete']);
        Route::post('email/delete/{email}', ['as' => 'mship.manage.email.delete.post', 'uses' => 'Management@postEmailDelete']);
        Route::get('email/assignments', ['as' => 'mship.manage.email.assignments', 'uses' => 'Management@getEmailAssignments']);
        Route::post('email/assignments', ['as' => 'mship.manage.email.assignments.post', 'uses' => 'Management@postEmailAssignments']);
    });

    Route::group(['middleware' => ['auth_full_group'], 'prefix' => 'feedback'], function () {
        Route::get('/new', ['as' => 'mship.feedback.new', 'uses' => 'Feedback@getFeedbackFormSelect']);
        Route::post('/new', ['as' => 'mship.feedback.new.post', 'uses' => 'Feedback@postFeedbackFormSelect']);
        Route::get('/new/{form}', ['as' => 'mship.feedback.new.form', 'uses' => 'Feedback@getFeedback']);
        Route::post('/new/{form}', ['as' => 'mship.feedback.new.form.post', 'uses' => 'Feedback@postFeedback']);

        Route::get('/users/search/{name}', ['as' => 'mship.feedback.usersearch', 'uses' => 'Feedback@getUserSearch']);
    });

    Route::group(['middleware' => ['auth_full_group']], function () {
        Route::get('/email', ['as' => 'mship.email', 'uses' => 'Email@getEmail']);
        Route::post('/email', ['as' => 'mship.email.post', 'uses' => 'Email@postEmail']);
        Route::get('/email/recipient-search', ['as' => 'mship.email.recipient-search', 'uses' => 'Email@getRecipientSearch']);
    });
});

// TeamSpeak

Route::group(['prefix' => 'mship/manage/teamspeak', 'namespace' => 'TeamSpeak', 'middleware' => ['auth_full_group']], function () {
    Route::model('tsreg', App\Models\TeamSpeak\Registration::class);
    Route::get('new', ['as' => 'teamspeak.new', 'uses' => 'Registration@getNew']);
    Route::get('success', ['as' => 'teamspeak.success', 'uses' => 'Registration@getConfirmed']);
    Route::get('{tsreg}/delete', ['as' => 'teamspeak.delete', 'uses' => 'Registration@getDelete']);
    Route::post('{tsreg}/status', ['as' => 'teamspeak.status', 'uses' => 'Registration@postStatus']);
});

Route::group(['prefix' => 'mship/manage/slack', 'namespace' => 'Slack', 'middleware' => ['auth_full_group']], function () {
    Route::model('slackToken', App\Models\Sys\Token::class);
    Route::get('/new', ['as' => 'slack.new', 'uses' => 'Registration@getNew']);
    Route::get('/success', ['as' => 'slack.success', 'uses' => 'Registration@getConfirmed']);
    Route::post('/{slackToken}/status', ['as' => 'slack.status', 'uses' => 'Registration@postStatus']);
});

// Community
Route::get('/community', function () {
    return redirect()->route('membership.deploy');
});

Route::group([
    'as' => 'community.',
    'namespace' => 'Community',
    'prefix' => 'community',
    'middleware' => ['auth_full_group'],
], function () {
    Route::group(['as' => 'membership.', 'prefix' => 'membership'], function () {
        Route::get('/deploy', [
            'as' => 'deploy',
            'uses' => 'Membership@getDeploy',
        ]);

        Route::post('/deploy/{default?}', [
            'as' => 'deploy.post',
            'uses' => 'Membership@postDeploy',
        ])->where('default', '[default|true]');
    });
});

// Network data

Route::get('/network-data', function () {
    return Redirect::route('networkdata.landing');
});

Route::group([
    'as' => 'networkdata.',
    'namespace' => 'NetworkData',
    'prefix' => 'network-data',
    'middleware' => ['auth_full_group'],
], function () {
    Route::get('/', function () {
        return redirect()->route('networkdata.online');
    })->name('landing');

    Route::get('/dashboard', 'MainController@getDashboard')->name('dashboard');
    Route::get('/online', ['as' => 'online', 'uses' => 'Online@getOnline']);
});

// Visit/Transfer

Route::get('/visiting-transferring', function () {
    return Redirect::route('visiting.landing');
});

Route::group([
    'as' => 'visiting.',
    'prefix' => 'visit-transfer',
    'namespace' => 'VisitTransfer\Site',
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

            Route::post('/facility/manual', [
                'as' => 'facility.manual.post',
                'uses' => 'Application@postManualFacility',
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

        Route::post('/complete/{token}/cancel', [
            'as' => 'complete.cancel',
            'uses' => 'Reference@postCancel',
        ]);
    });
});
