<?php

Route::group(array("prefix" => "statistics"), function () {
    Route::get("test", function(){
        return "HIYA!";
    });
});
