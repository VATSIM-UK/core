<?php

Route::group([
    "as"         => "smartcars.",
    "prefix"     => "smartcars",
    "namespace"  => "Api",
    "domain"     => config("app.url"),
    "middleware" => []
], function () {

    Route::get("/", function(){
        return "Script OK, Frame Version: VATSIM_UK_CUSTOM_1, Interface Version: VATSIM_UK_CUSTOM_1";
    });

    Route::group(["as" => "auth.", "prefix" => "/auth/", "namespace" => "Auth"], function(){
        Route::get("/manual", function(){
            
        });
    });

});
