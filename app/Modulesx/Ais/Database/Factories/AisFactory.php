<?php

$factory->define(App\Modules\Ais\Models\Aerodrome::class, function($faker){
    return [
        "sector_id" => factory(App\Modules\Ais\Models\Fir\Sector::class)->create()->id,
        "icao" => "EG".$faker->randomElement(["TE","LL","KK","BB","CC","NX","GW","SS"]),
        "iata" => $faker->randomLetter().$faker->randomLetter().$faker->randomLetter(),
        "name" => $faker->company(),
        "latitude" => $faker->latitude(),
        "longitude" => $faker->longitude(),
        "display" => $faker->boolean(95),
    ];
});

$factory->define(App\Modules\Ais\Models\Aerodrome\Facility::class, function($faker){
    return [
        "aerodrome_id" => factory(App\Modules\Ais\Models\Aerodrome::class)->create()->id,
        "facility_id" => factory(App\Modules\Ais\Models\Facility::class)->create()->id,
        "top_down_order" => $faker->numberBetween(1, 10),
    ];
});

$factory->define(App\Modules\Ais\Models\Facility::class, function($faker){
    return [
        "name" => $faker->company(),
    ];
});

$factory->define(App\Modules\Ais\Models\Facility\Position::class, function($faker){
    return [
        "facility_id" => factory(App\Modules\Ais\Models\Facility::class)->create()->id,
        "callsign" => $faker->text(10),
        "frequency" => $faker->numberBetween(118, 136).".".$faker->numberBetween(1, 985),
        "logon_order" => $faker->numberBetween(1, 10),
    ];
});

$factory->define(App\Modules\Ais\Models\Fir::class, function($faker){
    return [
        "icao" => $faker->randomElement(["EGTT","EGPX","EGGX"]),
        "name" => $faker->company(),
    ];
});

$factory->define(App\Modules\Ais\Models\Fir\Sector::class, function($faker){
    return [
        "fir_id" => factory(App\Modules\Ais\Models\Fir::class)->create()->id,
        "covered_by" => factory(App\Modules\Ais\Models\Fir\Sector::class)->create()->id,
        "name" => $faker->company(),
        "callsign_default" => $faker->text(10),
        "frequency" => $faker->numberBetween(118, 136).".".$faker->numberBetween(1, 985),
    ];
});