<?php

/*
 * VATS.IM ROUTES
 */
Route::group(['domain' => 'vats.im'], function () {
    Route::any('/', function () {
        return 'vats.im homepage';
    });

    Route::any('{request_url}', function ($request_url) {
        // check 'Request::path();' against model 'Route'
        $success = App\Models\Short\ShortURL::where('url', $request_url)->first();
        // if successful, redirect, else throw 404
        if ($success) {
            header("Location: {$success->forward_url}");
            exit();
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
    });
});

/*** WEBHOOKS ***/
Route::group(['domain' => config('app.url'), 'prefix' => 'webhook', 'namespace' => 'Webhook'], function () {
    Route::get('dropbox', ['as' => 'webhook.dropbox', 'uses' => 'Dropbox@getDropbox']);
    Route::post('dropbox', ['as' => 'webhook.dropbox.post', 'uses' => 'Dropbox@postDropbox']);
    Route::any('slack', ['as' => 'webhook.slack', 'uses' => 'Slack@anyRouter']);

    Route::group(['prefix' => 'email', 'namespace' => 'Email'], function () {
        //Route::any('mailgun', ['as' => 'webhook.email.mailgun', 'uses' => 'Mailgun@anyRoute']);
    });
});

/* * ** ADM *** */
Route::group(['namespace' => 'Adm', 'domain' => config('app.url')], function () {
    Route::group(['prefix' => 'adm'], function () {

        // Login is the only unauthenticated page.
        Route::get('/', ['middleware' => 'auth.admin', 'uses' => 'Authentication@getLogin']);
        Route::group(['prefix' => 'authentication'], function () {
            Route::get('/login', ['as' => 'adm.authentication.login', 'uses' => 'Authentication@getLogin']);
            Route::post('/login', ['as' => 'adm.authentication.login.post', 'uses' => 'Authentication@postLogin']);
            Route::get('/logout', ['as' => 'adm.authentication.logout', 'uses' => 'Authentication@getLogout']);
            Route::get('/verify', ['as' => 'adm.authentication.verify', 'uses' => 'Authentication@getVerify']);
        });

        Route::get('/error/{code?}', ['as' => 'adm.error', 'uses' => 'Error@getDisplay']);

        // Auth required
        Route::group(['middleware' => 'auth.admin'], function () {
            Route::get('/dashboard', ['as' => 'adm.dashboard', 'uses' => 'Dashboard@getIndex']);
            Route::any('/search/{q?}', ['as' => 'adm.search', 'uses' => 'Dashboard@anySearch']);

            Route::group(['prefix' => 'system', 'namespace' => 'Sys'], function () {
                Route::get('/activity', ['as' => 'adm.sys.activity.list', 'uses' => 'Activity@getIndex']);

                Route::get('/module', ['as' => 'adm.sys.module.list', 'uses' => 'Module@getIndex']);
                Route::get('/module/{slug}/enable', ['as' => 'adm.sys.module.enable', 'uses' => 'Module@getEnable']);
                Route::get('/module/{slug}/disable', ['as' => 'adm.sys.module.disable', 'uses' => 'Module@getDisable']);

                Route::get('/jobs/failed', ['as' => 'adm.sys.jobs.failed', 'uses' => 'Jobs@getFailed']);
                Route::post('/jobs/failed/{id}/retry', ['as' => 'adm.sys.jobs.failed.retry', 'uses' => 'Jobs@postFailed']);
                Route::delete('/jobs/failed/{id}/delete', ['as' => 'adm.sys.jobs.failed.delete', 'uses' => 'Jobs@deleteFailed']);
            });

            Route::group(['prefix' => 'mship', 'namespace' => 'Mship'], function () {
                /* Route::get('/airport/{navdataAirport}', 'Airport@getDetail')->where(array('navdataAirport' => '\d'));
                  Route::post('/airport/{navdataAirport}', 'Airport@getDetail')->where(array('navdataAirport' => '\d')); */
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

                Route::get('/staff', ['as' => 'adm.mship.staff.index', 'uses' => 'Staff@getIndex']);
            });
        });
    });
});

Route::group(['domain' => config('app.url')], function () {
    Route::get('/error/{code?}', ['as' => 'error', 'uses' => 'Error@getDisplay']);

    Route::group(['prefix' => 'mship', 'namespace' => 'Mship'], function () {
        // Guest access
        Route::group(['prefix' => 'auth'], function () {
            Route::get('/redirect', ['as' => 'mship.auth.redirect', 'uses' => 'Authentication@getRedirect']);
            Route::get('/login-alternative', ['as' => 'mship.auth.loginAlternative', 'uses' => 'Authentication@getLoginAlternative']);
            Route::post('/login-alternative', ['as' => 'mship.auth.loginAlternative.post', 'uses' => 'Authentication@postLoginAlternative']);
            Route::get('/login', ['as' => 'mship.auth.login', 'uses' => 'Authentication@getLogin']);
            Route::get('/verify', ['as' => 'mship.auth.verify', 'uses' => 'Authentication@getVerify']);
            Route::get('/logout/{force?}', ['as' => 'mship.auth.logout', 'uses' => 'Authentication@getLogout']);
            Route::post('/logout/{force?}', ['as' => 'mship.auth.logout.post', 'uses' => 'Authentication@postLogout']);

            // /mship/auth - fully authenticated.
            Route::group(['middleware' => ['auth_full_group']], function () {
                Route::get('/invisibility', ['as' => 'mship.auth.invisibility', 'uses' => 'Authentication@getInvisibility']);
            });
        });

        Route::group(['middleware' => 'auth.user.full', 'prefix' => 'notification'], function () {
            Route::get('/list', ['as' => 'mship.notification.list', 'uses' => 'Notification@getList']);
            Route::post('/acknowledge/{sysNotification}', ['as' => 'mship.notification.acknowledge', 'uses' => 'Notification@postAcknowledge']);
        });

        Route::group(['prefix' => 'manage'], function () {
            Route::get('/landing', ['as' => 'mship.manage.landing', 'uses' => 'Management@getLanding']);
            Route::get('/dashboard', [
                'as' => 'mship.manage.dashboard',
                'uses' => 'Management@getDashboard',
                'middleware' => ['auth_full_group'],
            ]);

            Route::group(['prefix' => 'email'], function () {
                Route::get('/verify/{code}', ['as' => 'mship.manage.email.verify', 'uses' => 'Management@getVerifyEmail']);

                Route::group(['middleware' => ['auth_full_group']], function () {
                    Route::get('/add', ['as' => 'mship.manage.email.add', 'uses' => 'Management@getEmailAdd']);
                    Route::post('/add', ['as' => 'mship.manage.email.add.post', 'uses' => 'Management@postEmailAdd']);

                    Route::get('/delete/{email}', ['as' => 'mship.manage.email.delete', 'uses' => 'Management@getEmailDelete']);
                    Route::post('/delete/{email}', ['as' => 'mship.manage.email.delete.post', 'uses' => 'Management@postEmailDelete']);

                    Route::get('/assignments', ['as' => 'mship.manage.email.assignments', 'uses' => 'Management@getEmailAssignments']);
                    Route::post('/assignments', ['as' => 'mship.manage.email.assignments.post', 'uses' => 'Management@postEmailAssignments']);
                });
            });
        });

        Route::group(['middleware' => ['auth_full_group'], 'prefix' => 'feedback'], function () {
            Route::get('/new', ['as' => 'mship.feedback.new', 'uses' => 'Feedback@getFeedbackFormSelect']);
            Route::post('/new', ['as' => 'mship.feedback.new.post', 'uses' => 'Feedback@postFeedbackFormSelect']);
            Route::get('/new/{form}', ['as' => 'mship.feedback.new.form', 'uses' => 'Feedback@getFeedback']);
            Route::post('/new/{form}', ['as' => 'mship.feedback.new.form.post', 'uses' => 'Feedback@postFeedback']);
        });

        Route::group(['prefix' => 'security'], function () {
            Route::get('/forgotten-link/{code}', ['as' => 'mship.security.forgotten.link', 'uses' => 'Security@getForgottenLink'])->where(['code' => '\w+']);

            Route::group(['middleware' => 'auth.user'], function () {
                Route::get('/forgotten', ['as' => 'mship.security.forgotten', 'uses' => 'Security@getForgotten']);
                Route::get('/auth', ['as' => 'mship.security.auth', 'uses' => 'Security@getAuth']);
                Route::post('/auth', ['as' => 'mship.security.auth.post', 'uses' => 'Security@postAuth']);
                Route::get('/replace/{delete?}', ['as' => 'mship.security.replace', 'uses' => 'Security@getReplace'])->where(['delete' => '[1|0]']);
                Route::post('/replace/{delete?}', ['as' => 'mship.security.replace.post', 'uses' => 'Security@postReplace'])->where(['delete' => '[1|0]']);
            });

            Route::group(['middleware' => ['auth_full_group']], function () {
                Route::get('/enable', ['as' => 'mship.security.enable', 'uses' => 'Security@getEnable']);
            });
        });
    });

    Route::group(['prefix' => 'mship/manage/teamspeak', 'namespace' => 'TeamSpeak', 'middleware' => ['auth_full_group']], function () {
        Route::model('tsreg', App\Models\TeamSpeak\Registration::class);
        Route::get('/new', ['as' => 'teamspeak.new', 'uses' => 'Registration@getNew']);
        Route::get('/success', ['as' => 'teamspeak.success', 'uses' => 'Registration@getConfirmed']);
        Route::get('/{tsreg}/delete', ['as' => 'teamspeak.delete', 'uses' => 'Registration@getDelete']);
        Route::post('/{tsreg}/status', ['as' => 'teamspeak.status', 'uses' => 'Registration@postStatus']);
    });

    Route::group(['prefix' => 'mship/manage/slack', 'namespace' => 'Slack', 'middleware' => ['auth_full_group']], function () {
        Route::model('slackToken', App\Models\Sys\Token::class);
        Route::get('/new', ['as' => 'slack.new', 'uses' => 'Registration@getNew']);
        Route::get('/success', ['as' => 'slack.success', 'uses' => 'Registration@getConfirmed']);
        Route::post('/{slackToken}/status', ['as' => 'slack.status', 'uses' => 'Registration@postStatus']);
    });

    Route::group(['prefix' => 'sso', 'namespace' => 'Sso'], function () {
        Route::get('auth/login', ['middleware' => 'user.must.read.notifications', 'as' => 'sso.auth.login', 'uses' => 'Authentication@getLogin']);
        Route::post('security/generate', ['as' => 'sso.security.generate', 'uses' => 'Security@postGenerate']);
        Route::post('security/details', ['as' => 'sso.security.details', 'uses' => 'Security@postDetails']);
    });
});

Route::get('/', ['domain' => config('app.url'), 'as' => 'default', function () {
    return Redirect::route('mship.manage.landing');
}]);
