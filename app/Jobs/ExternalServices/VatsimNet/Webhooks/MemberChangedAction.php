<?php

namespace App\Jobs\ExternalServices\VatsimNet\Webhooks;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class MemberChangedAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Account $account;
    protected Collection $data;

    public function __construct(int $memberId, array $data)
    {
        $this->account = Account::with('states', 'qualifications')->findOrFail($memberId);
        $this->data = collect($data);
    }

    public function handle()
    {
        foreach ($this->data->get('deltas') as $delta) {
            match ($delta['field']) {
                'id', 'name_first', 'name_last', 'email', 'reg_date' => $this->processAccountChange($delta['field'], $delta['after']),
                'rating' => $this->processAtcRatingChange($delta['after']),
                'pilotrating' => $this->processPilotRatingChange($delta['after']),
                'division_id', 'region_id' => $this->processStateChange(),
                default => null
            };
        }
    }

    private function processAccountChange(string $field, mixed $value): void
    {
        $this->account->update([
            $field => $value,
        ]);
    }

    private function processAtcRatingChange(mixed $value): void
    {
        $this->account->updateVatsimRatings(atcRating: $value);
    }

    private function processPilotRatingChange(mixed $value): void
    {
        $this->account->updateVatsimRatings(pilotRating: $value);
    }

    private function processStateChange(): void
    {
        $currentRegion = $this->account->primary_permanent_state->pivot->region;
        $currentDivision = $this->account->primary_permanent_state->pivot->division;

        $regionChange = collect($this->data['deltas'])->firstWhere('field', 'region_id');
        $divisionChange = collect($this->data['deltas'])->firstWhere('field', 'division_id');

        $this->account->updateDivision(
            division: is_null($divisionChange) ? $currentDivision : $divisionChange['after'],
            region: is_null($regionChange) ? $currentRegion : $regionChange['after'],
        );
    }
}
