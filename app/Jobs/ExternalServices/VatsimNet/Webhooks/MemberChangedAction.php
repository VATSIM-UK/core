<?php

namespace App\Jobs\ExternalServices\VatsimNet\Webhooks;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MemberChangedAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $memberId;

    protected array $data;

    public function __construct(int $memberId, array $data)
    {
        $this->memberId = $memberId;
        $this->data = $data;
    }

    public function handle()
    {
        foreach ($this->data['deltas'] as $delta) {
            match ($delta['field']) {
                'id', 'name_first', 'name_last', 'email', 'reg_date' => $this->processAccountChange($delta['field'], $delta['after']),
                'rating' => $this->processAtcRatingChange($delta['after']),
                'pilotrating' => $this->processPilotRatingChange($delta['after']),
                'division_id', 'region_id' => $this->processStateChange($delta['after']),
                default => null
            };
        }
    }

    private function processAccountChange(string $field, mixed $value): void
    {
        Account::firstWhere('id', $this->memberId)->update([
            $field => $value,
        ]);
    }

    private function processAtcRatingChange(mixed $value): void
    {
        Account::firstWhere('id', $this->memberId)->updateVatsimRatings(atcRating: $value);
    }

    private function processPilotRatingChange(mixed $value): void
    {
        Account::firstWhere('id', $this->memberId)->updateVatsimRatings(pilotRating: $value);
    }

    private function processStateChange(mixed $value): void
    {
        // if both a division and region is changed in the deltas
        // this will run twice, which is not ideal

        $account = Account::with('states')->firstWhere('id', $this->memberId);

        $currentRegion = $account->primary_permanent_state->pivot->region;
        $currentDivision = $account->primary_permanent_state->pivot->division;

        $regionChange = collect($this->data['deltas'])->firstWhere('field', 'region_id');
        $divisionChange = collect($this->data['deltas'])->firstWhere('field', 'division_id');

        $account->updateDivision(
            division: is_null($divisionChange) ? $currentDivision : $divisionChange['after'],
            region: is_null($regionChange) ? $currentRegion : $regionChange['after'],
        );
    }
}
