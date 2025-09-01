<?php

namespace App\Jobs\ExternalServices\VatsimNet\Webhooks;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MemberCreatedAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $memberId, private readonly array $data) {}

    public function handle(): void
    {
        $fields = $this->collectFields();

        $account = Account::updateOrCreate(['id' => $this->getField($fields, 'id')], [
            'name_first' => $this->getField($fields, 'name_first'),
            'name_last' => $this->getField($fields, 'name_last'),
            'email' => $this->getField($fields, 'email'),
            'joined_at' => $this->getField($fields, 'reg_date'),
        ]);
        $account->updateVatsimRatings($this->getField($fields, 'rating'), $this->getField($fields, 'pilotrating'));
        $account->updateDivision($this->getField($fields, 'division_id'), $this->getField($fields, 'region_id'));
        $account->save();
    }

    private function collectFields(): Collection
    {
        return collect($this->data['deltas']);
    }

    private function getField(Collection $fields, string $name): mixed
    {
        $field = $fields->firstWhere('field', $name);
        if (! $field) {
            return null;
        }

        return Arr::get($field, 'after');
    }
}
