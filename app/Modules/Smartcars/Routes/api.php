<?php

Route::group([
    "as"         => "api.smartcars.",
    "prefix"     => "smartcars",
    "namespace"  => "Api",
    "domain"     => config("app.url"),
    "middleware" => []
], function () {

    Route::get("/call", function(){
        switch(Request::get("action")){
            case "manuallogin":
                return redirect()->route("api.smartcars.auth.manual", Request::all());
            case "automaticlogin":
                return redirect()->route("api.smartcars.auth.auto", Request::all());
            case "verifysession":
                return redirect()->route("api.smartcars.auth.verify", Request::all());
            case "getpilotcenterdata":
                return "0,0,0,0";

            case "getairports":
                return null;
            case "getaircraft":
                return null;
            case "getbidflights":
                return null;
            case "bidonflight":
                return null;
            case "deletebidflight":
                return null;
            case "searchpireps":
                return null;
            case "getpirepdata":
                return null;
            case "searchflights":
                return null;
            case "createflight":
                return null;
            case "positionreport":
                return null;
            case "filepirep":
                return null;
        }
    })->name("call");

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

    Route::get("/", function(){
        return "Script OK, Frame Version: VATSIM_UK_CUSTOM_1, Interface Version: VATSIM_UK_CUSTOM_1";
    });

});
