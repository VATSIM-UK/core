<?php

namespace Database\Factories\Mship;

use App\Models\Mship\Qualification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mship\Qualification>
 */
class QualificationFactory extends Factory
{
    use WithFaker;

    protected $model = Qualification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code' => $this->findUniqueQualificationCode('?##'),
            'name_small' => $this->faker->word,
            'name_long' => $this->faker->word,
            'name_grp' => $this->faker->word,
            'vatsim' => $this->faker->randomDigit,
        ];
    }

    public function atc(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'atc',
                'code' => $this->findUniqueQualificationCode('C##')
            ];
        });
    }

    public function pilot(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'pilot',
                'code' => $this->findUniqueQualificationCode('P##')
            ];
        });
    }

    public function findUniqueQualificationCode($pattern)
    {
        $foundUniqueCode = false;
        $code = null;
        while (!$foundUniqueCode) {
            $code = $this->faker->bothify($pattern);
            if (!Qualification::code($code)->exists()) $foundUniqueCode = true;
        }
        return $code;
    }
}
