<?php

use App\Models\Cts\Member;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Cts\ExaminerSettings::class, function (Faker $faker) {
    return [
        'memberID' => factory(Member::class)->create(['examiner' => 1])->id,
        'OBS' => rand(0, 1),
        'S1' => rand(0, 1),
        'S2' => rand(0, 1),
        'S3' => rand(0, 1),
        'P1' => rand(0, 1),
        'P2' => rand(0, 1),
        'P3' => rand(0, 1),
        'P4' => rand(0, 1),
        'P5' => rand(0, 1),
        'OBStrain' => rand(0, 1),
        'S1train' => rand(0, 1),
        'S2train' => rand(0, 1),
        'S3train' => rand(0, 1),
        'P1train' => rand(0, 1),
        'P2train' => rand(0, 1),
        'P3train' => rand(0, 1),
        'P4train' => rand(0, 1),
        'P5train' => rand(0, 1),
        'lastUpdated' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
        'updatedBy' => 1111111,
    ];
});
