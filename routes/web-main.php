<?php

// Index
Route::get('/', 'Mship\Management@getLanding')->name('default');

// Authentication
Route::get('login', 'Auth\LoginController@getLogin');
Route::post('login', 'Auth\LoginController@loginMain')->name('login');
Route::get('login-secondary', 'Auth\LoginController@showLoginForm')->name('auth-secondary');
Route::post('login-secondary', 'Auth\LoginController@loginSecondary')->name('auth-secondary.post');
Route::get('login-vatsim', 'Auth\LoginController@vatsimSsoReturn')->name('auth-vatsim-sso');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Password Reset
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

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

// Admin panel
Route::group(['prefix' => 'adm', 'namespace' => 'Adm', 'middleware' => ['auth_full_group']], function () {
    // Index
    Route::get('/', function () {
        return redirect()->route('adm.dashboard');
    });

    // Main
    Route::get('/dashboard', ['as' => 'adm.dashboard', 'uses' => 'Dashboard@getIndex']);
    Route::any('/search/{q?}', ['as' => 'adm.search', 'uses' => 'Dashboard@anySearch']);

    // System
    Route::group(['prefix' => 'system', 'namespace' => 'Sys'], function () {
        Route::get('/activity', ['as' => 'adm.sys.activity.list', 'uses' => 'Activity@getIndex']);

        Route::get('/jobs/failed', ['as' => 'adm.sys.jobs.failed', 'uses' => 'Jobs@getFailed']);
        Route::post('/jobs/failed/{id}/retry', ['as' => 'adm.sys.jobs.failed.retry', 'uses' => 'Jobs@postFailed']);
        Route::delete('/jobs/failed/{id}/delete', ['as' => 'adm.sys.jobs.failed.delete', 'uses' => 'Jobs@deleteFailed']);
    });

    // Members
    Route::group(['prefix' => 'mship', 'namespace' => 'Mship'], function () {
        Route::get('/account/{mshipAccount}/{tab?}/{tabid?}', ['as' => 'adm.mship.account.details', 'uses' => 'Account@getDetail'])->where(['mshipAccount' => '\d+']);
        Route::post('/account/{mshipAccount}/roles/attach', ['as' => 'adm.mship.account.role.attach', 'uses' => 'Account@postRoleAttach'])->where(['mshipAccount' => '\d+']);
        Route::get('/account/{mshipAccount}/roles/{mshipRole}/detach', ['as' => 'adm.mship.account.role.detach', 'uses' => 'Account@getRoleDetach'])->where(['mshipAccount' => '\d+']);
        Route::post('/account/{mshipAccount}/ban/add', ['as' => 'adm.mship.account.ban.add', 'uses' => 'Account@postBanAdd'])->where(['mshipAccount' => '\d+']);
        Route::post('/account/{mshipAccount}/note/create', ['as' => 'adm.mship.account.note.create', 'uses' => 'Account@postNoteCreate'])->where(['mshipAccount' => '\d+']);
        Route::post('/account/{mshipAccount}/note/filter', ['as' => 'adm.mship.account.note.filter', 'uses' => 'Account@postNoteFilter'])->where(['mshipAccount' => '\d+']);
        Route::post('/account/{mshipAccount}/security/enable', ['as' => 'adm.mship.account.security.enable', 'uses' => 'Account@postSecurityEnable'])->where(['mshipAccount' => '\d+']);
        Route::post('/account/{mshipAccount}/security/reset', ['as' => 'adm.mship.account.security.reset', 'uses' => 'Account@postSecurityReset'])->where(['mshipAccount' => '\d+']);
        Route::post('/account/{mshipAccount}/security/change', ['as' => 'adm.mship.account.security.change', 'uses' => 'Account@postSecurityChange'])->where(['mshipAccount' => '\d+']);
        Route::post('/account/{mshipAccount}/impersonate', ['as' => 'adm.mship.account.impersonate', 'uses' => 'Account@postImpersonate'])->where(['mshipAccount' => '\d+']);

        Route::get('/ban/{ban}/repeal', ['as' => 'adm.mship.ban.repeal', 'uses' => 'Account@getBanRepeal'])->where(['ban' => '\d+']);
        Route::post('/ban/{ban}/repeal', ['as' => 'adm.mship.ban.repeal.post', 'uses' => 'Account@postBanRepeal'])->where(['ban' => '\d+']);

        Route::get('/ban/{ban}/comment', ['as' => 'adm.mship.ban.comment', 'uses' => 'Account@getBanComment'])->where(['ban' => '\d+']);
        Route::post('/ban/{ban}/comment', ['as' => 'adm.mship.ban.comment.post', 'uses' => 'Account@postBanComment'])->where(['ban' => '\d+']);

        Route::get('/ban/{ban}/modify', ['as' => 'adm.mship.ban.modify', 'uses' => 'Account@getBanModify'])->where(['ban' => '\d+']);
        Route::post('/ban/{ban}/modify', ['as' => 'adm.mship.ban.modify.post', 'uses' => 'Account@postBanModify'])->where(['ban' => '\d+']);

        Route::get('/account/{scope?}', ['as' => 'adm.mship.account.index', 'uses' => 'Account@getIndex'])->where(['scope' => '\w+']);

        Route::get('/role/create', ['as' => 'adm.mship.role.create', 'uses' => 'Role@getCreate']);
        Route::post('/role/create', ['as' => 'adm.mship.role.create.post', 'uses' => 'Role@postCreate']);
        Route::get('/role/{mshipRole}/update', ['as' => 'adm.mship.role.update', 'uses' => 'Role@getUpdate']);
        Route::post('/role/{mshipRole}/update', ['as' => 'adm.mship.role.update.post', 'uses' => 'Role@postUpdate']);
        Route::any('/role/{mshipRole}/delete', ['as' => 'adm.mship.role.delete', 'uses' => 'Role@anyDelete']);
        Route::get('/role/', ['as' => 'adm.mship.role.index', 'uses' => 'Role@getIndex']);

        Route::get('/permission/create', ['as' => 'adm.mship.permission.create', 'uses' => 'Permission@getCreate']);
        Route::post('/permission/create', ['as' => 'adm.mship.permission.create.post', 'uses' => 'Permission@postCreate']);
        Route::get('/permission/{mshipPermission}/update', ['as' => 'adm.mship.permission.update', 'uses' => 'Permission@getUpdate']);
        Route::post('/permission/{mshipPermission}/update', ['as' => 'adm.mship.permission.update.post', 'uses' => 'Permission@postUpdate']);
        Route::any('/permission/{mshipPermission}/delete', ['as' => 'adm.mship.permission.delete', 'uses' => 'Permission@anyDelete']);
        Route::get('/permission/', ['as' => 'adm.mship.permission.index', 'uses' => 'Permission@getIndex']);

        Route::group(['as' => 'adm.mship.note.'], function () {
            Route::get('/note/type/create', ['as' => 'type.create', 'uses' => 'Note@getTypeCreate']);
            Route::post('/note/type/create', ['as' => 'type.create.post', 'uses' => 'Note@postTypeCreate']);
            Route::get('/note/type/{mshipNoteType}/update', ['as' => 'type.update', 'uses' => 'Note@getTypeUpdate']);
            Route::post('/note/type/{mshipNoteType}/update', ['as' => 'type.update.post', 'uses' => 'Note@postTypeUpdate']);
            Route::any('/note/type/{mshipNoteType}/delete', ['as' => 'type.delete', 'uses' => 'Note@anyTypeDelete']);
            Route::get('/note/type/', ['as' => 'type.index', 'uses' => 'Note@getTypeIndex']);
        });

        Route::group(['prefix' => 'feedback', 'as' => 'adm.mship.feedback.'], function () {
            Route::get('configure/{form}', ['as' => 'config', 'uses' => 'Feedback@getConfigure']);
            Route::post('configure/{form}', ['as' => 'config.save', 'uses' => 'Feedback@postConfigure']);

            Route::get('list', ['as' => 'all', 'uses' => 'Feedback@getAllFeedback']);
            Route::get('list/atc', ['as' => 'atc', 'uses' => 'Feedback@getATCFeedback']);
            Route::get('list/pilot', ['as' => 'pilot', 'uses' => 'Feedback@getPilotFeedback']);
            Route::get('view/{feedback}', ['as' => 'view', 'uses' => 'Feedback@getViewFeedback']);
            Route::post('view/{feedback}/action', ['as' => 'action', 'uses' => 'Feedback@postActioned']);
            Route::get('view/{feedback}/unaction', ['as' => 'unaction', 'uses' => 'Feedback@getUnActioned']);
        });

        Route::get('staff', ['as' => 'adm.mship.staff.index', 'uses' => 'Staff@getIndex']);
    });
});

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

/*
 * COMMUNITY
 */
Route::get('/community', function () {
    return Redirect::route('membership.deploy');
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

/*
 * NETWORK DATA
 */

Route::get('/network-data', function () {
    return Redirect::route('networkdata.landing');
});

Route::group([
    'as' => 'networkdata.admin.',
    'namespace' => 'NetworkData',
    'prefix' => 'adm/network-data',
    'middleware' => ['auth_full_group'],
], function () {
    Route::get('/', [
        'as' => 'dashboard',
        'uses' => 'Dashboard@getDashboard',
    ]);
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

    Route::get('/online', ['as' => 'online', 'uses' => 'Online@getOnline']);
});

/*
 * VISITING & TRANSFERRING
 */
Route::get('/visiting-transferring', function () {
    return Redirect::route('visiting.landing');
});

Route::group([
    'as' => 'visiting.admin.',
    'prefix' => 'adm/visit-transfer',
    'namespace' => 'VisitTransfer\Admin',
    'middleware' => ['auth_full_group'],
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

/*
 * SmartCARS ROUTES
 */
Route::any('frame.php', function () {
    \Log::info(\Request::method().'::'.\Request::fullUrl());
    \Log::info(\Request::all());
    if (\Request::method() == 'POST') {
        $return = \App::call(\App\Http\Controllers\Smartcars\Api\Router::class.'@postRoute', Request::all());
    } else {
        $return = \App::call(\App\Http\Controllers\Smartcars\Api\Router::class.'@getRoute', Request::all());
    }
    \Log::info($return);

    return $return;
});
