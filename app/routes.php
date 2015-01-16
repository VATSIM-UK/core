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

Route::model("mshipAccount", "\Models\Mship\Account\Account", function() {
    Redirect::to("/adm/mship/account");
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
