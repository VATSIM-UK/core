<?php

namespace App\Jobs\ExternalServices\VatsimNet\Webhooks\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait InteractsWithMemberDeltas
{
    private ?Collection $deltaCollection = null;

    private ?Collection $changedFieldMap = null;

    /**
     * Return deltas from the webhook payload as a collection.
     */
    private function deltas(): Collection
    {
        if ($this->deltaCollection instanceof Collection) {
            return $this->deltaCollection;
        }

        $this->deltaCollection = collect($this->data['deltas'] ?? []);

        return $this->deltaCollection;
    }

    /**
     * Fetch the `after` value for a specific field change from webhook deltas.
     */
    private function getDeltaAfter(string $field): mixed
    {
        return $this->changedFields()->get($field);
    }

    /**
     * Build a keyed map of changed field => after value from webhook deltas.
     */
    private function changedFields(): Collection
    {
        if ($this->changedFieldMap instanceof Collection) {
            return $this->changedFieldMap;
        }

        $this->changedFieldMap = $this->deltas()
            ->mapWithKeys(fn (array $delta) => [Arr::get($delta, 'field') => Arr::get($delta, 'after')])
            ->filter(fn (mixed $value, mixed $key) => ! is_null($key));

        return $this->changedFieldMap;
    }
}
