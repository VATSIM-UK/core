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

            Route::group(array("prefix" => "system"), function(){
                Route::get("/timeline", array("as" => "adm.system.timeline", "uses" => "System@getTimeline"));
            });

            Route::group(array("prefix" => "mship", "namespace" => "Mship"), function() {
                /* Route::get("/airport/{navdataAirport}", "Airport@getDetail")->where(array("navdataAirport" => "\d"));
                  Route::post("/airport/{navdataAirport}", "Airport@getDetail")->where(array("navdataAirport" => "\d")); */
                Route::get("/account", ["as" => "adm.account.index", "uses" => "Account@getIndex"]);
                Route::get("/account/{mshipAccount}/{tab?}", ["as" => "adm.account.details", "uses" => "Account@getDetail"]);
                Route::post("/account/{mshipAccount}/security/enable", ["as" => "adm.account.security.enable", "uses" => "Account@postSecurityEnable"]);
                Route::post("/account/{mshipAccount}/security/reset", ["as" => "adm.account.security.reset", "uses" => "Account@postSecurityReset"]);
                Route::post("/account/{mshipAccount}/security/change", ["as" => "adm.account.security.change", "uses" => "Account@postSecurityChange"]);
            });
        });
    });
});

Route::group(array("namespace" => "Controllers"), function() {
    Route::group(array("prefix" => "mship", "namespace" => "Mship"), function() {
        // Guest access
        Route::controller("authentication", "Authentication");
        Route::controller("auth", "Authentication"); // Legacy URL.  **DO NOT REMOVE**
        Route::get("manage/landing", "Management@get_landing");

        // No auth needed for reset.
        Route::get("security/forgotten-link/{code}", "Security@get_forgotten_link")->where(array("code" => "\w+"));
        Route::group(array("before" => "auth.user.basic"), function() {
            Route::controller("security", "Security");
        });

        Route::group(array("before" => "auth.user.full"), function() {
            Route::get("manage/display", "Management@get_dashboard"); // Legacy URL.  **DO NOT REMOVE**
            Route::controller("manage", "Management");
        });
    });

    Route::group(array("prefix" => "sso", "namespace" => "Sso"), function() {
        Route::controller("auth", "Authentication");
        Route::controller("security", "Security");
    });
});

Route::get("/", "\Controllers\Mship\Management@get_landing");
