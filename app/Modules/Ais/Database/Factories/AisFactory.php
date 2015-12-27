<?php

$factory->defineAs(App\Modules\Ais\Models\Aerodrome::class, function($faker){
    return [
        "sector_id" => null,
        "icao" => "EG".$faker->randomLetter().$faker->randomLetter(),
        "iata" => $faker->randomLetter().$faker->randomLetter().$faker->randomLetter(),

    ];
});
