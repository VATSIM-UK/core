<?php

Route::group(['domain' => 'vats.im'], function () {
    Route::any('/', function () {
        return "vats.im homepage";
    });

    Route::any('{request_url}', function ($request_url) {
        // check 'Request::path();' against model 'Route'
        $success = \App\Models\Short\ShortURL::where('url', '=', $request_url)->first();
        // if successful, redirect, else throw 404
        if ($success) {
            header("Location: {$success->forward_url}");
            exit();
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
    });
});

Route::model("mshipAccount", "\App\Models\Mship\Account", function () {
    Redirect::route("adm.mship.account.index");
});

Route::model("mshipAccountEmail", "\App\Models\Mship\Account\Email");
Route::model("ssoEmail", "\App\Models\Sso\Email");
Route::model("sysNotification", "\App\Models\Sys\Notification");

Route::model("mshipRole", "\App\Models\Mship\Role", function () {
    Redirect::route("adm.mship.role.index")->withError("Role doesn't exist.");
});

Route::model("mshipPermission", "\App\Models\Mship\Permission", function () {
    Redirect::route("adm.mship.permission.index")->withError("Permission doesn't exist.");
});

Route::model("postmasterQueue", "\App\Models\Sys\Postmaster\Queue", function () {
    Redirect::route("adm.sys.postmaster.queue.index");
});

Route::model("postmasterTemplate", "\App\Models\Sys\Postmaster\Template", function () {
    Redirect::route("adm.sys.postmaster.template.index");
});

/*** WEBHOOKS ***/
Route::group(["prefix" => "webhook", "namespace" => "Webhook"], function () {
    Route::group(["prefix" => "email", "namespace" => "Email"], function () {
        Route::any("mailgun", ["as" => "webhook.email.mailgun", "uses" => "Mailgun@anyRoute"]);
    });
});

/* * ** ADM *** */
Route::group(array("namespace" => "Adm"), function () {
    Route::group(array("prefix" => "adm"), function () {

        // Login is the only unauthenticated page.
        Route::get("/", array("uses" => "Authentication@getLogin"));
        Route::group(array("prefix" => "authentication"), function () {
            Route::get("/login", array("as" => "adm.authentication.login", "uses" => "Authentication@getLogin"));
            Route::post("/login", array("as" => "adm.authentication.login", "uses" => "Authentication@postLogin"));
            Route::get("/logout", array("as" => "adm.authentication.logout", "uses" => "Authentication@getLogout"));
            Route::get("/verify", array("as" => "adm.authentication.verify", "uses" => "Authentication@getVerify"));
        });

        Route::get("/error/{code?}", ["as" => "adm.error", "uses" => "Error@getDisplay"]);

        // Auth required
        Route::group(array("before" => "auth.admin"), function () {
            Route::get("/dashboard", array("as" => "adm.dashboard", "uses" => "Dashboard@getIndex"));
            Route::any("/search/{q?}", array("as" => "adm.search", "uses" => "Dashboard@anySearch"));

            Route::group(array("prefix" => "system", "namespace" => "Sys"), function () {
                Route::get("/timeline", array("as" => "adm.sys.timeline", "uses" => "Timeline@getIndex"));

                Route::group(["prefix" => "postmaster", "namespace" => "Postmaster"], function () {
                    Route::get("/queue", ["as" => "adm.sys.postmaster.queue.index", "uses" => "Queue@getIndex"]);
                    Route::get("/queue/{postmasterQueue}", ["as" => "adm.sys.postmaster.queue.view", "uses" => "Queue@getView"]);
                    Route::get("/template", ["as" => "adm.sys.postmaster.template.index", "uses" => "Template@getIndex"]);
                    Route::get("/template/{postmasterTemplate}", ["as" => "adm.sys.postmaster.template.view", "uses" => "Template@getView"]);
                });
            });

            Route::group(array("prefix" => "mship", "namespace" => "Mship"), function () {
                /* Route::get("/airport/{navdataAirport}", "Airport@getDetail")->where(array("navdataAirport" => "\d"));
                  Route::post("/airport/{navdataAirport}", "Airport@getDetail")->where(array("navdataAirport" => "\d")); */
                Route::get("/account/{mshipAccount}/{tab?}", ["as" => "adm.mship.account.details", "uses" => "Account@getDetail"])->where(["mshipAccount" => "\d+"]);
                Route::post("/account/{mshipAccount}/role/attach", ["as" => "adm.mship.account.role.attach", "uses" => "Account@postRoleAttach"])->where(["mshipAccount" => "\d+"]);
                Route::post("/account/{mshipAccount}/role/{mshipRole}/detach", ["as" => "adm.mship.account.role.detach", "uses" => "Account@postRoleDetach"])->where(["mshipAccount" => "\d+"]);
                Route::post("/account/{mshipAccount}/note/create", ["as" => "adm.mship.account.note.create", "uses" => "Account@postNoteCreate"])->where(["mshipAccount" => "\d+"]);
                Route::post("/account/{mshipAccount}/note/filter", ["as" => "adm.mship.account.note.filter", "uses" => "Account@postNoteFilter"])->where(["mshipAccount" => "\d+"]);
                Route::post("/account/{mshipAccount}/security/enable", ["as" => "adm.mship.account.security.enable", "uses" => "Account@postSecurityEnable"])->where(["mshipAccount" => "\d+"]);
                Route::post("/account/{mshipAccount}/security/reset", ["as" => "adm.mship.account.security.reset", "uses" => "Account@postSecurityReset"])->where(["mshipAccount" => "\d+"]);
                Route::post("/account/{mshipAccount}/security/change", ["as" => "adm.mship.account.security.change", "uses" => "Account@postSecurityChange"])->where(["mshipAccount" => "\d+"]);
                Route::post("/account/{mshipAccount}/impersonate", ["as" => "adm.mship.account.impersonate", "uses" => "Account@postImpersonate"])->where(["mshipAccount" => "\d+"]);
                Route::get("/account/{scope?}", ["as" => "adm.mship.account.index", "uses" => "Account@getIndex"])->where(["scope" => "\w+"]);

                Route::get("/role/create", ["as" => "adm.mship.role.create", "uses" => "Role@getCreate"]);
                Route::post("/role/create", ["as" => "adm.mship.role.create", "uses" => "Role@postCreate"]);
                Route::get("/role/{mshipRole}/update", ["as" => "adm.mship.role.update", "uses" => "Role@getUpdate"]);
                Route::post("/role/{mshipRole}/update", ["as" => "adm.mship.role.update", "uses" => "Role@postUpdate"]);
                Route::any("/role/{mshipRole}/delete", ["as" => "adm.mship.role.delete", "uses" => "Role@anyDelete"]);
                Route::get("/role/", ["as" => "adm.mship.role.index", "uses" => "Role@getIndex"]);

                Route::get("/permission/create", ["as" => "adm.mship.permission.create", "uses" => "Permission@getCreate"]);
                Route::post("/permission/create", ["as" => "adm.mship.permission.create", "uses" => "Permission@postCreate"]);
                Route::get("/permission/{mshipPermission}/update", ["as" => "adm.mship.permission.update", "uses" => "Permission@getUpdate"]);
                Route::post("/permission/{mshipPermission}/update", ["as" => "adm.mship.permission.update", "uses" => "Permission@postUpdate"]);
                Route::any("/permission/{mshipPermission}/delete", ["as" => "adm.mship.permission.delete", "uses" => "Permission@anyDelete"]);
                Route::get("/permission/", ["as" => "adm.mship.permission.index", "uses" => "Permission@getIndex"]);

                Route::get('/staff', ["as" => "adm.mship.staff.index", "uses" => "Staff@getIndex"]);

            });
        });
    });
});

Route::group([], function () {
    Route::get("/error/{code?}", ["as" => "error", "uses" => "Error@getDisplay"]);

    Route::group(array("prefix" => "mship", "namespace" => "Mship"), function () {
        // Guest access
        Route::group(array("prefix" => "auth"), function () {
            Route::get("/redirect", ["as" => "mship.auth.redirect", "uses" => "Authentication@getRedirect"]);
            Route::get("/login-alternative", ["as" => "mship.auth.loginAlternative", "uses" => "Authentication@getLoginAlternative"]);
            Route::post("/login-alternative", ["as" => "mship.auth.loginAlternative", "uses" => "Authentication@postLoginAlternative"]);
            Route::get("/login", ["as" => "mship.auth.login", "uses" => "Authentication@getLogin"]);
            Route::get("/verify", ["as" => "mship.auth.verify", "uses" => "Authentication@getVerify"]);
            Route::get("/logout/{force?}", ["as" => "mship.auth.logout", "uses" => "Authentication@getLogout"]);
            Route::post("/logout/{force?}", ["as" => "mship.auth.logout", "uses" => "Authentication@postLogout"]);

            // /mship/auth - fully authenticated.
            Route::group(["before" => ["auth.user.full", "user.must.read.notifications"]], function () {
                Route::get("/invisibility", ["as" => "mship.auth.invisibility", "uses" => "Authentication@getInvisibility"]);
            });
        });

        Route::group(["before" => "auth.user.full", "prefix" => "notification"], function(){
            Route::get("/list", ["as" => "mship.notification.list", "uses" => "Notification@getList"]);
            Route::post("/acknowledge/{sysNotification}", ["as" => "mship.notification.acknowledge", "uses" => "Notification@postAcknowledge"]);
        });

        Route::group(["prefix" => "manage"], function () {
            Route::get("/landing", ["as" => "mship.manage.landing", "uses" => "Management@getLanding"]);
            Route::get("/dashboard", [
                "as" => "mship.manage.dashboard",
                "uses" => "Management@getDashboard",
                "before" => ["auth.user.full", "user.must.read.notifications"],
            ]);

            Route::group(["prefix" => "email"], function(){

                Route::get("/verify/{code}", ["as" => "mship.manage.email.verify", "uses" => "Management@getVerifyEmail"]);

                Route::group(["before" => ["auth.user.full", "user.must.read.notifications"]], function(){
                    Route::get("/add", ["as" => "mship.manage.email.add", "uses" => "Management@getEmailAdd"]);
                    Route::post("/add", ["as" => "mship.manage.email.add", "uses" => "Management@postEmailAdd"]);

                    Route::get("/delete", ["as" => "mship.manage.email.delete", "uses" => "Management@getEmailDelete"]);
                    Route::post("/delete", ["as" => "mship.manage.email.delete", "uses" => "Management@postEmailDelete"]);

                    Route::get("/assignments", ["as" => "mship.manage.email.assignments", "uses" => "Management@getEmailAssignments"]);
                    Route::post("/assignments", ["as" => "mship.manage.email.assignments", "uses" => "Management@postEmailAssignments"]);
                });
            });
        });

        Route::group(["prefix" => "security"], function () {
            Route::get("/forgotten-link/{code}", ["as" => "mship.security.forgotten.link", "uses" => "Security@getForgottenLink"])->where(array("code" => "\w+"));

            Route::group(["before" => "auth.user"], function(){
                Route::get("/forgotten", ["as" => "mship.security.forgotten", "uses" => "Security@getForgotten"]);
                Route::get("/auth", ["as" => "mship.security.auth", "uses" => "Security@getAuth"]);
                Route::post("/auth", ["as" => "mship.security.auth", "uses" => "Security@postAuth"]);
                Route::get("/replace/{delete?}", ["as" => "mship.security.replace", "uses" => "Security@getReplace"])->where(array("delete" => "[1|0]"));
                Route::post("/replace/{delete?}", ["as" => "mship.security.replace", "uses" => "Security@postReplace"])->where(array("delete" => "[1|0]"));
            });

            Route::group(["before" => ["auth.user.full", "user.must.read.notifications"]], function(){
                Route::get("/enable", ["as" => "mship.security.enable", "uses" => "Security@getEnable"]);
            });
        });
    });

    Route::group(["prefix" => "mship/manage/teamspeak", "namespace" => "Teamspeak", "before" => ["auth.user.full", "user.must.read.notifications"]], function () {
        Route::model('tsreg', '\App\Models\Teamspeak\Registration');
        Route::get("/new", ["as" => "teamspeak.new", "uses" => "Registration@getNew"]);
        Route::get("/success", ["as" => "teamspeak.success", "uses" => "Registration@getConfirmed"]);
        Route::get("/{tsreg}/delete", ["as" => "teamspeak.delete", "uses" => "Registration@getDelete"]);
        Route::post("/{tsreg}/status", ["as" => "teamspeak.status", "uses" => "Registration@postStatus"]);
    });

    Route::group(array("prefix" => "sso", "namespace" => "Sso"), function () {
        Route::get("auth/login", ["before" => "user.must.read.notifications", "as" => "sso.auth.login", "uses" => "Authentication@getLogin"]);
        Route::post("security/generate", ["as" => "sso.security.generate", "uses" => "Security@postGenerate"]);
        Route::post("security/details", ["as" => "sso.security.details", "uses" => "Security@postDetails"]);
    });
});

Route::get("/", function () {
    return Redirect::route("mship.manage.landing");
});
