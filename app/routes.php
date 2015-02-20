<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

Route::model("mshipAccount", "\Models\Mship\Account", function() {
    Redirect::to("/adm/mship/account");
});

/* * ** ADM *** */
Route::group(array("namespace" => "Controllers\Adm"), function() {
    Route::group(array("prefix" => "adm"), function() {
        // Login is the only unauthenticated page.
        Route::get("/", array("uses" => "Authentication@getLogin"));
        Route::group(array("prefix" => "authentication"), function(){
            Route::get("/login", array("as" => "adm.authentication.login", "uses" => "Authentication@getLogin"));
            Route::post("/login", array("as" => "adm.authentication.login", "uses" => "Authentication@postLogin"));
            Route::get("/logout", array("as" => "adm.authentication.logout", "uses" => "Authentication@getLogout"));
            Route::get("/verify", array("as" => "adm.authentication.verify", "uses" => "Authentication@getVerify"));
        });

        // Auth required
        Route::group(array("before" => "auth.admin"), function() {
            Route::get("/dashboard", array("as" => "adm.dashboard", "uses" => "Dashboard@getIndex"));
            Route::any("/search/{q?}", array("as" => "adm.search", "uses" => "Dashboard@anySearch"));

            Route::group(array("prefix" => "system", "namespace" => "Sys"), function(){
                Route::get("/timeline", array("as" => "adm.sys.timeline", "uses" => "System@getTimeline"));

                Route::group(["prefix" => "postmaster", "namespace" => "Postmaster"], function(){
                    Route::get("/queue", ["as" => "adm.sys.postmaster.queue.index", "uses" => "Queue@getIndex"]);
                    Route::get("/queue/view/{queueID}", ["as" => "adm.sys.postmaster.queue.view", "uses" => "Queue@getView"]);
                    Route::get("/template", ["as" => "adm.sys.postmaster.template.index", "uses" => "Template@getIndex"]);
                    Route::get("/template/view/{templateID}", ["as" => "adm.sys.postmaster.template.view", "uses" => "Template@getView"]);
                });
            });

            Route::group(array("prefix" => "mship", "namespace" => "Mship"), function() {
                /* Route::get("/airport/{navdataAirport}", "Airport@getDetail")->where(array("navdataAirport" => "\d"));
                  Route::post("/airport/{navdataAirport}", "Airport@getDetail")->where(array("navdataAirport" => "\d")); */
                Route::get("/account/{sortBy?}/{sortDir?}/", ["as" => "adm.mship.account.index", "uses" => "Account@getIndex"]);
                Route::get("/account/{mshipAccount}/{tab?}", ["as" => "adm.mship.account.details", "uses" => "Account@getDetail"]);
                Route::post("/account/{mshipAccount}/security/enable", ["as" => "adm.mship.account.security.enable", "uses" => "Account@postSecurityEnable"]);
                Route::post("/account/{mshipAccount}/security/reset", ["as" => "adm.mship.account.security.reset", "uses" => "Account@postSecurityReset"]);
                Route::post("/account/{mshipAccount}/security/change", ["as" => "adm.mship.account.security.change", "uses" => "Account@postSecurityChange"]);
            });
        });
    });
});

Route::group(array("namespace" => "Controllers"), function() {
    Route::group(array("prefix" => "mship", "namespace" => "Mship"), function() {
        // Guest access
        Route::group(array("prefix" => "auth"), function(){
            Route::get("/redirect", ["as" => "mship.auth.redirect", "uses" => "Authentication@getRedirect"]);
            Route::get("/login", ["as" => "mship.auth.login", "uses" => "Authentication@getLogin"]);
            Route::get("/logout/{force?}", ["as" => "mship.auth.logout", "uses" => "Authentication@getLogout"]);
            Route::post("/logout/{force?}", ["as" => "mship.auth.logout", "uses" => "Authentication@postLogout"]);
            Route::get("/verify", ["as" => "mship.auth.verify", "uses" => "Authentication@getVerify"]);

            // /mship/auth - fully authenticated.
            Route::group(["before" => "auth.user.full"], function(){
                Route::get("/override", ["as" => "mship.auth.override", "uses" => "Authentication@getOverride"]);
                Route::post("/override", ["as" => "mship.auth.override", "uses" => "Authentication@postOverride"]);
                Route::get("/invisibility", ["as" => "mship.auth.invisibility", "uses" => "Authentication@getInvisibility"]);
            });
        });

        Route::group(["prefix" => "manage"], function(){
            Route::get("/landing", ["as" => "mship.manage.landing", "uses" => "Management@getLanding"]);
            Route::get("/dashboard", [
                "as" => "mship.manage.dashboard",
                "uses" => "Management@getDashboard",
                "before" => "auth.user.full",
                ]);
        });

        Route::group(["prefix" => "security"], function(){
            Route::get("/forgotten-link/{code}", ["as" => "mship.security.forgotten.link", "uses" => "Security@getForgottenLink"])->where(array("code" => "\w+"));

            Route::group(["before" => "auth.user.basic"], function(){
                Route::get("/auth", ["as" => "mship.security.auth", "uses" => "Security@getAuth"]);
                Route::post("/auth", ["as" => "mship.security.auth", "uses" => "Security@postAuth"]);
                Route::get("/enable", ["as" => "mship.security.enable", "uses" => "Security@getEnable"]);
                Route::get("/replace", ["as" => "mship.security.replace", "uses" => "Security@getReplace"]);
                Route::post("/replace", ["as" => "mship.security.replace", "uses" => "Security@postReplace"]);
                Route::get("/forgotten", ["as" => "mship.security.forgotten", "uses" => "Security@getForgotten"]);
            });
        });
    });

    Route::group(array("prefix" => "sso", "namespace" => "Sso"), function() {
        Route::controller("auth", "Authentication");
        Route::controller("security", "Security");
    });
});

Route::get("/", "/mship/landing");
