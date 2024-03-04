<?php

$factory->define(\App\Models\VisitTransfer\Reference::class, function ($faker) {
    return [
        'account_id' => function () {
            return \App\Models\Mship\Account::factory()->create()->id;
        },
        'application_id' => function () {
            return factory(\App\Models\VisitTransfer\Application::class)->create()->id;
        },
        'email' => $faker->email,
        'relationship' => $faker->randomElement(['Region Director', 'Region Staff', 'Division Director', 'Division Training Director', 'Division Staff', 'VACC/ARTCC Director', 'VACC/ARTCC Training Director', 'VACC/ARTCC Staff']),
        'status' => \App\Models\VisitTransfer\Reference::STATUS_DRAFT,
    ];
});
