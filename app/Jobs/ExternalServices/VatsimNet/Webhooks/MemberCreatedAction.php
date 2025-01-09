<?php

namespace App\Jobs\ExternalServices\VatsimNet\Webhooks;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class MemberCreatedAction implements ShouldQueue
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
        $account = Account::updateOrCreate(['id' => $this->getField('id')], [
            'name_first' => $this->getField('name_first'),
            'name_last' => $this->getField('name_last'),
            'email' => $this->getField('email'),
            'joined_at' => $this->getField('reg_date'),
        ]);
        $account->updateVatsimRatings($this->getField('rating'), $this->getField('pilotrating'));
        $account->updateDivision($this->getField('division_id'), $this->getField('region_id'));
        $account->save();
    }

    private function getField(string $field)
    {
        return Arr::get(collect($this->data['deltas'])->firstWhere('field', $field), 'after');
    }
}
