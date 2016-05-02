<?php

Route::group(["as" => "visiting.", "prefix" => "visiting-transferring"], function () {
    Route::get("/", ["as" => "landing", "uses" => "Dashboard@getDashboard"]);

    Route::group(["as" => "application.", "prefix" => "application"], function () {
        Route::get("", function () {
            return Redirect::route("visiting.landing");
        });

        Route::get("/start/{type}", [
            "as"   => "start",
            "uses" => "Application@getStart"
        ])->where("type", "\d+");

        Route::post("/start/{type}", [
            "as"   => "start.post",
            "uses" => "Application@postStart"
        ])->where("type", "\d+");

        Route::get("/continue", [
            "as" => "continue",
            function () {
                return "You are at: " . route("visiting.application.continue");
            }
        ]);

        Route::get("/facility", [
            "as" => "facility",
            "uses" => "Application@getFacility",
        ]);

        Route::post("/facility", [
            "as" => "facility.post",
            "uses" => "Application@postFacility",
        ]);

        Route::get("/statement", [
            "as" => "statement",
            "uses" => "Application@getStatement",
        ]);

        Route::post("/statement", [
            "as" => "statement.post",
            "uses" => "Application@postStatement",
        ]);

        Route::get("/referees", [
            "as" => "referees",
            function () {
                return "You are at: " . route("visiting.application.referees");
            }
        ]);

        Route::get("/submit", [
            "as" => "referees",
            function () {
                return "You are at: " . route("visiting.application.referees");
            }
        ]);

        Route::get("/history", [
            "as" => "history",
            function () {
                return "You are at: " . route("visiting.application.history");
            }
        ]);

        Route::get("/view", [
            "as" => "view",
            function () {
                return "You are at: " . route("visiting.application.view");
            }
        ]);
    });

    Route::group(["as" => "reference.", "prefix" => "reference"], function () {
        Route::get("/", [
            "as" => "landing",
            function () {
                return "You are at: " . route("visiting.reference.landing");
            }
        ]);

        Route::get("/complete", [
            "as" => "complete",
            function () {
                return "You are at: " . route("visiting.reference.complete");
            }
        ]);
    });
});
