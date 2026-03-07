<?php

namespace App\Jobs\ExternalServices\VatsimNet\Webhooks;

use App\Jobs\ExternalServices\VatsimNet\Webhooks\Concerns\InteractsWithMemberDeltas;
use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MemberCreatedAction implements ShouldQueue
{
    use Dispatchable, InteractsWithMemberDeltas, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  int  $memberId  VATSIM member CID the action applies to
     * @param  array<string, mixed>  $data  Webhook action payload
     */
    public function __construct(private readonly int $memberId, private readonly array $data) {}

    /**
     * Create or update account details from a member creation webhook action.
     */
    public function handle(): void
    {
        $account = Account::updateOrCreate(['id' => $this->getDeltaAfter('id')], [
            'name_first' => $this->getDeltaAfter('name_first'),
            'name_last' => $this->getDeltaAfter('name_last'),
            'email' => $this->getDeltaAfter('email'),
            'joined_at' => $this->getDeltaAfter('reg_date'),
        ]);

        $account->updateVatsimRatings($this->getDeltaAfter('rating'), $this->getDeltaAfter('pilotrating'));
        $account->updateDivision($this->getDeltaAfter('division_id'), $this->getDeltaAfter('region_id'));
        $account->save();

        Log::debug('Processed VATSIM.net member_created_action', [
            'resource' => $this->memberId,
            'account_id' => $account->id,
        ]);
    }
}
