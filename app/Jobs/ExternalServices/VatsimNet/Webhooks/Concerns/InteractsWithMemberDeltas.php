<?php

namespace App\Jobs\ExternalServices\VatsimNet\Webhooks\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait InteractsWithMemberDeltas
{
    /**
     * Return deltas from the webhook payload as a collection.
     */
    private function deltas(): Collection
    {
        return collect($this->data['deltas'] ?? []);
    }

    /**
     * Fetch the `after` value for a specific field change from webhook deltas.
     */
    private function getDeltaAfter(string $field): mixed
    {
        $delta = $this->deltas()->firstWhere('field', $field);

        return Arr::get($delta, 'after');
    }

    /**
     * Build a keyed map of changed field => after value from webhook deltas.
     */
    private function changedFields(): Collection
    {
        return $this->deltas()
            ->mapWithKeys(fn (array $delta) => [Arr::get($delta, 'field') => Arr::get($delta, 'after')])
            ->filter(fn (mixed $value, mixed $key) => ! is_null($key));
    }
}
