<?php

Route::group(["as" => "visiting.", "prefix" => "visiting-transferring", "namespace" => "vt"], function () {
    Route::get("/", ["as" => "landing", function(){
        return "You are at: " . route("visiting.landing");
    }]);

    Route::group(["as" => "application.", "prefix" => "application"], function(){
        Route::get("", function(){
            return Redirect::route("visiting.landing");
        });

        Route::get("/terms", ["as" => "start", function(){
            return "You are at: " . route("visiting.application.start");
        }]);

        Route::get("/facility", ["as" => "facility", function(){
            return "You are at: " . route("visiting.application.facility");
        }]);

        Route::get("/statement", ["as" => "statement", function(){
            return "You are at: " . route("visiting.application.statement");
        }]);

        Route::get("/referees", ["as" => "referees", function(){
            return "You are at: " . route("visiting.application.referees");
        }]);

        Route::get("/submit", ["as" => "referees", function(){
            return "You are at: " . route("visiting.application.referees");
        }]);

        Route::get("/history", ["as" => "history", function(){
            return "You are at: " . route("visiting.application.history");
        }]);

        Route::get("/view", ["as" => "view", function(){
            return "You are at: " . route("visiting.application.view");
        }]);
    });

    Route::group(["as" => "reference.", "prefix" => "reference"], function(){
        Route::get("/", ["as" => "landing", function(){
            return "You are at: " . route("visiting.reference.landing");
        }]);

        Route::get("/complete", ["as" => "complete", function(){
            return "You are at: " . route("visiting.reference.complete");
        }]);
    });
});
