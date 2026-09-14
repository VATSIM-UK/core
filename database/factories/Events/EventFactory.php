<?php

namespace Database\Factories\Events;

use App\Enums\EventChecklistItem;
use App\Models\Events\Event;
use App\Models\Mship\Account;
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
            'published_by' => null,
            'manager_id' => null,
        ];
    }

    public function published(?Account $publisher = null): static
    {
        return $this->state(fn () => [
            'published_at' => now(),
            'published_by' => $publisher?->id,
        ]);
    }

    public function withChecklistItem(EventChecklistItem $item, ?Account $completedBy = null): static
    {
        return $this->afterCreating(function (Event $event) use ($item, $completedBy) {
            $event->checklistCompletions()->create([
                'item' => $item->value,
                'account_id' => $completedBy?->id,
                'completed_at' => now(),
            ]);

            $event->unsetRelation('checklistCompletions');
        });
    }
}
