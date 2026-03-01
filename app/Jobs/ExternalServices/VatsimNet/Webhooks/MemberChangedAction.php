<?php

namespace App\Jobs\ExternalServices\VatsimNet\Webhooks;

use App\Jobs\ExternalServices\VatsimNet\Webhooks\Concerns\InteractsWithMemberDeltas;
use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MemberChangedAction implements ShouldQueue
{
    use Dispatchable, InteractsWithMemberDeltas, InteractsWithQueue, Queueable, SerializesModels;

    private const ACCOUNT_FIELDS = ['id', 'name_first', 'name_last', 'email', 'reg_date'];

    private Account $account;

    /**
     * @param  int  $memberId  VATSIM member CID the action applies to
     * @param  array<string, mixed>  $data  Webhook action payload
     */
    public function __construct(private readonly int $memberId, private readonly array $data) {}

    /**
     * Apply delta-based changes to an existing account record.
     */
    public function handle(): void
    {
        $this->account = Account::findOrFail($this->memberId);
        $changes = $this->changedFields();

        $this->processAccountChanges($changes);
        $this->processRatingChanges($changes);

        if ($changes->has('division_id') || $changes->has('region_id')) {
            $this->processStateChange();
        }

        Log::debug('Processed VATSIM.net member_changed_action', [
            'resource' => $this->memberId,
            'changed_fields' => $changes->keys()->values(),
        ]);
    }

    /**
     * Persist scalar account fields that actually changed in the payload.
     */
    private function processAccountChanges(Collection $changes): void
    {
        $accountChanges = $changes
            ->only(self::ACCOUNT_FIELDS)
            ->reject(fn (mixed $value, string $field) => $this->account->getAttribute($field) === $value);

        if ($accountChanges->isNotEmpty()) {
            $this->account->update($accountChanges->all());
        }
    }

    /**
     * Update ATC and pilot qualifications when related deltas are present.
     */
    private function processRatingChanges(Collection $changes): void
    {
        if ($changes->has('rating')) {
            $this->account->updateVatsimRatings(atcRating: $changes->get('rating'));
        }

        if ($changes->has('pilotrating')) {
            $this->account->updateVatsimRatings(pilotRating: $changes->get('pilotrating'));
        }
    }

    /**
     * Update region/division, preserving current values when one side is omitted.
     */
    private function processStateChange(): void
    {
        $currentStatePivot = $this->account->primary_permanent_state->pivot;

        $this->account->updateDivision(
            division: $this->getDeltaAfter('division_id') ?? $currentStatePivot->division,
            region: $this->getDeltaAfter('region_id') ?? $currentStatePivot->region,
        );
    }
}
