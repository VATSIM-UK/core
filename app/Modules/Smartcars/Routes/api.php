<?php

Route::group([
    "as"         => "api.smartcars.",
    "prefix"     => "smartcars",
    "namespace"  => "Api",
    "domain"     => config("app.url"),
    "middleware" => []
], function () {

    Route::get("/call", [
        "as"   => "call",
        "uses" => "Router@getRoute",
    ]);

    Route::post("/call", [
        "as"   => "call.post",
        "uses" => "Router@postRoute",
    ]);

    Route::group(["as" => "auth.", "prefix" => "auth/"], function(){

        Route::post("/manual", [
            "as"   => "manual",
            "uses" => "Authentication@postManual",
        ]);

        Route::post("/auto", [
            "as"   => "auto",
            "uses" => "Authentication@postAuto",
        ]);

        Route::post("/verify", [
            "as" => "verify",
            "uses" => "Authentication@postVerify",
        ]);

    });
});
