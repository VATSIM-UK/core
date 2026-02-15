<?php

namespace Database\Factories\VisitTransfer;

use App\Models\VisitTransfer\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferenceFactory extends Factory
{
    protected $model = Reference::class;

    public function definition()
    {
        return [
            'account_id' => \App\Models\Mship\Account::factory(),
            'application_id' => \App\Models\VisitTransfer\Application::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'relationship' => $this->faker->randomElement(['Region Director', 'Region Staff', 'Division Director', 'Division Training Director', 'Division Staff', 'VACC/ARTCC Director', 'VACC/ARTCC Training Director', 'VACC/ARTCC Staff']),
            'status' => Reference::STATUS_DRAFT,
        ];
    }
}
