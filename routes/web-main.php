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
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email')->middleware('auth:vatsim-sso');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset')->middleware('auth:vatsim-sso');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.request')->middleware('auth:vatsim-sso');

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
Route::group(['prefix' => 'mship', 'namespace' => 'Mship', 'middleware' => 'auth_full_group'], function () {
    Route::post('auth/invisibility', ['as' => 'mship.auth.invisibility', 'uses' => 'Management@postInvisibility']);

    Route::get('notification/list', ['as' => 'mship.notification.list', 'uses' => 'Notification@getList']);
    Route::post('notification/acknowledge/{sysNotification}', ['as' => 'mship.notification.acknowledge', 'uses' => 'Notification@postAcknowledge']);

    Route::get('manage/dashboard', ['as' => 'mship.manage.dashboard', 'uses' => 'Management@getDashboard']);
    Route::get('manage/email/verify/{code}', ['as' => 'mship.manage.email.verify', 'uses' => 'Management@getVerifyEmail']);
    Route::get('manage/email/add', ['as' => 'mship.manage.email.add', 'uses' => 'Management@getEmailAdd']);
    Route::post('manage/email/add', ['as' => 'mship.manage.email.add.post', 'uses' => 'Management@postEmailAdd']);
    Route::get('manage/email/delete/{email}', ['as' => 'mship.manage.email.delete', 'uses' => 'Management@getEmailDelete']);
    Route::post('manage/email/delete/{email}', ['as' => 'mship.manage.email.delete.post', 'uses' => 'Management@postEmailDelete']);
    Route::get('manage/email/assignments', ['as' => 'mship.manage.email.assignments', 'uses' => 'Management@getEmailAssignments']);
    Route::post('manage/email/assignments', ['as' => 'mship.manage.email.assignments.post', 'uses' => 'Management@postEmailAssignments']);

    Route::get('feedback/new', ['as' => 'mship.feedback.new', 'uses' => 'Feedback@getFeedbackFormSelect']);
    Route::post('feedback/new', ['as' => 'mship.feedback.new.post', 'uses' => 'Feedback@postFeedbackFormSelect']);
    Route::get('feedback/new/{form}', ['as' => 'mship.feedback.new.form', 'uses' => 'Feedback@getFeedback']);
    Route::post('feedback/new/{form}', ['as' => 'mship.feedback.new.form.post', 'uses' => 'Feedback@postFeedback']);
    Route::get('feedback/users/search/{name}', ['as' => 'mship.feedback.usersearch', 'uses' => 'Feedback@getUserSearch']);

    Route::get('/email', ['as' => 'mship.email', 'uses' => 'Email@getEmail']);
    Route::post('/email', ['as' => 'mship.email.post', 'uses' => 'Email@postEmail']);
    Route::get('/email/recipient-search', ['as' => 'mship.email.recipient-search', 'uses' => 'Email@getRecipientSearch']);
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
Route::group(['as' => 'community.membership.', 'namespace' => 'Community', 'middleware' => 'auth_full_group'], function () {
    Route::get('community/membership/deploy', [
        'as' => 'deploy',
        'uses' => 'Membership@getDeploy',
    ]);

    Route::post('community/membership/deploy/{default?}', [
        'as' => 'deploy.post',
        'uses' => 'Membership@postDeploy',
    ])->where('default', '[default|true]');
});

// Network data
Route::group(['as' => 'networkdata.', 'namespace' => 'NetworkData', 'middleware' => 'auth_full_group'], function () {
    Route::get('network-data', function () {
        return redirect()->route('networkdata.online');
    })->name('landing');

    Route::get('network-data/dashboard', 'MainController@getDashboard')->name('dashboard');
    Route::get('network-data/online', ['as' => 'online', 'uses' => 'Online@getOnline']);
});

// Visit/Transfer
Route::get('visiting-transferring', function () {
    return redirect()->route('visiting.landing');
});

Route::group([
    'as' => 'visiting.',
    'prefix' => 'visit-transfer',
    'namespace' => 'VisitTransfer\Site',
    'middleware' => ['auth_full_group'],
], function () {
    Route::get('/', ['as' => 'landing', 'uses' => 'Dashboard@getDashboard']);

    Route::group(['as' => 'application.', 'prefix' => 'application'], function () {
        Route::get('/', function () {
            return redirect()->route('visiting.landing');
        });

        Route::get('start/{type}/{team}', 'Application@getStart')->where('type', "\d+")->name('start');
        Route::post('start/{type}/{team}', 'Application@postStart')->where('type', "\d+")->name('start.post');

        Route::group(['prefix' => '{applicationByPublicId}'], function () {
            Route::get('/', 'Application@getView')->name('view');
            Route::get('continue', 'Application@getContinue')->name('continue');
            Route::get('facility', 'Application@getFacility')->name('facility');
            Route::post('facility', 'Application@postFacility')->name('facility.post');
            Route::post('facility/manual', 'Application@postManualFacility')->name('facility.manual.post');
            Route::get('statement', 'Application@getStatement')->name('statement');
            Route::post('statement', 'Application@postStatement')->name('statement.post');
            Route::get('referees', 'Application@getReferees')->name('referees');
            Route::post('referees', 'Application@postReferees')->name('referees.post');
            Route::post('referees/{reference}/delete', 'Application@postRefereeDelete')->name('referees.delete.post');
            Route::get('submit', 'Application@getSubmit')->name('submit');
            Route::post('submit', 'Application@postSubmit')->name('submit.post');
            Route::get('withdraw', 'Application@getWithdraw')->name('withdraw');
            Route::post('withdraw', 'Application@postWithdraw')->name('withdraw.post');
        });
    });

    Route::group(['as' => 'reference.', 'prefix' => 'reference'], function () {
        Route::get('complete/{token}', 'Reference@getComplete')->name('complete');
        Route::post('complete/{token}', 'Reference@postComplete')->name('complete.post');
        Route::post('complete/{token}/cancel', 'Reference@postCancel')->name('complete.cancel');
    });
});
