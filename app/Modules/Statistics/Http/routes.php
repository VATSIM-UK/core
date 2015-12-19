<?php

Route::group(array("prefix" => "statistics"), function () {

    Route::get("dashboard", [
        "as" => "statistics.dashboard",
        "uses" => "Dashboard@getIndex",
    ]);

});
