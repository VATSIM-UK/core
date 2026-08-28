<?php

namespace Database\Factories\Events;

use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 day', '+30 days')
            ->setTime($this->faker->numberBetween(6, 21), $this->faker->randomElement([0, 15, 30, 45]), 0);

        return [
            'name' => 'Test event',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'image_url' => $this->faker->url,
            'start' => $start,
            'end' => (clone $start)->modify('+3 hours'),
            'rostered' => false,
            'published_at' => null,
            'manager_id' => null,
            'eoi_published' => false,
            'roster_published' => false,
            'briefing_published' => false,
            'briefing_created' => false,
            'banner_created' => false,
            'ecfmp_set_up' => false,
            'my_vatsim_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['published_at' => now()]);
    }
}
