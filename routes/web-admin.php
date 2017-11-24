<?php

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
            Route::get('new', ['as' => 'new', 'uses' => 'Feedback@getNewForm']);
            Route::post('new', ['as' => 'new.create', 'uses' => 'Feedback@postNewForm']);

            Route::get('configure/{form}', ['as' => 'config', 'uses' => 'Feedback@getConfigure']);
            Route::post('configure/{form}', ['as' => 'config.save', 'uses' => 'Feedback@postConfigure']);
            Route::get('configure/{form}/toggle', ['as' => 'config.toggle', 'uses' => 'Feedback@getEnableDisableForm']);
            Route::get('configure/{form}/visibility', ['as' => 'config.visibility', 'uses' => 'Feedback@getFormVisibility']);

            Route::get('list', ['as' => 'all', 'uses' => 'Feedback@getAllFeedback']);
            Route::get('list/{slug}', ['as' => 'form', 'uses' => 'Feedback@getFormFeedback']);
            Route::get('view/{feedback}', ['as' => 'view', 'uses' => 'Feedback@getViewFeedback']);
            Route::post('view/{feedback}/action', ['as' => 'action', 'uses' => 'Feedback@postActioned']);
            Route::get('view/{feedback}/unaction', ['as' => 'unaction', 'uses' => 'Feedback@getUnActioned']);
        });

        Route::get('staff', ['as' => 'adm.mship.staff.index', 'uses' => 'Staff@getIndex']);
    });
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
