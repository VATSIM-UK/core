<?php

namespace Database\Factories\Mship\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'account_id' => Account::factory(),
            'submitter_account_id' => Account::factory(),
        ];
    }
}
