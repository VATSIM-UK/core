<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use App\Notifications\Mship\WelcomeMember;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ImportDivisionMembers extends Command
{
    protected $signature = 'import:division-members';

    protected $description = 'Import VATSIM UK division members from the VATSIM API.';

    protected int $limit = 1000;

    protected int $offset = 0;

    protected int $countNewlyCreated = 0;

    protected int $countUpdated = 0;

    protected int $countSkipped = 0;

    public function handle()
    {
        while ($response = $this->getMembersFromVatsim()) {
            if ($response->collect('items')->isEmpty()) {
                $this->info('No more users to process.');

                return;
            }

            $this->info("Processing offset $this->offset containing {$response->collect('items')->count()} users out of {$response->json('count')}.");

            foreach ($response->collect('items') as $member) {
                $this->process($member);
            }

            $this->offset += $this->limit;

            $this->info("Finished offset $this->offset. New: $this->countNewlyCreated, updated: $this->countUpdated, skipped: $this->countSkipped.");
        }

        $this->info("Successfully created {$this->countNewlyCreated} new, updated {$this->countUpdated} and skipped {$this->countSkipped} members.");
    }

    protected function process(array $member)
    {
        $validator = Validator::make($member, [
            'id' => 'required|integer',
            'rating' => 'required|integer',
            'pilotrating' => 'required|int',
            'name_first' => 'required|string',
            'name_last' => 'required|string',
            'email' => 'required|email',
            'reg_date' => 'required|date',
            'region_id' => 'required|string',
            'division_id' => 'required|string',
        ]);

        return $validator->fails() ? $this->countSkipped++ : $this->update($member);
    }

    protected function update(array $member)
    {
        $account = Account::updateOrCreate(
            ['id' => $member['id']],
            [
                'name_first' => $member['name_first'],
                'name_last' => $member['name_last'],
                'email' => $member['email'],
                'joined_at' => Carbon::create($member['reg_date']),
                'inactive' => (int) $member['rating'] < 0,
                'cert_checked_at' => now(),
            ]
        );

        $account->updateVatsimRatings($member['rating'], $member['pilotrating']);
        $account->updateDivision($member['division_id'], $member['region_id']);

        $account->wasRecentlyCreated ?? $account->notify(new WelcomeMember());
        $account->wasRecentlyCreated ? $this->countNewlyCreated++ : $this->countUpdated++;
    }

    private function getMembersFromVatsim()
    {
        $token = config('vatsim-api.key');

        return Http::withHeaders([
            'Authorization' => "Token $token",
        ])->withUserAgent('VATSIMUK')
            ->get(config('vatsim-api.base').'orgs/division/GBR', [
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);
    }
}
