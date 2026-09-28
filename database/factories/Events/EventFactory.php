<?php

namespace Database\Factories\Events;

use App\Enums\EventChecklistItem;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use Carbon\Carbon;
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
            'end' => fn (array $attributes) => Carbon::parse($attributes['start'])->addHours(3),
            'rostered' => false,
            'published_at' => null,
            'published_by' => null,
        ];
    }

    public function withManagers(Account ...$managers): static
    {
        return $this->afterCreating(function (Event $event) use ($managers): void {
            $event->managers()->attach(
                collect($managers)->pluck('id')->all()
            );

            $event->unsetRelation('managers');
        });
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
