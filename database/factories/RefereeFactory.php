<?php

$factory->define(\App\Models\VisitTransfer\Reference::class, function ($faker) {
    return [
        'account_id' => factory(\App\Models\Mship\Account::class)->create()->id,
        'email' => $faker->email,
        'relationship' => $faker->randomElement(['Region Director', 'Region Staff', 'Division Director', 'Division Training Director', 'Division Staff', 'VACC/ARTCC Director', 'VACC/ARTCC Training Director', 'VACC/ARTCC Staff']),
        'status' => \App\Models\VisitTransfer\Reference::STATUS_DRAFT,
    ];
});
