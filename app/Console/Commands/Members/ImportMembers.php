<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use App\Notifications\Mship\WelcomeMember;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ImportMembers extends Command
{
    protected $signature = 'Members:CertImport';

    protected $description = 'Import VATSIM UK members from the VATSIM API.';

    protected string $apiToken;
    protected PendingRequest $apiRequest;

    protected int $countNewlyCreated = 0;
    protected int $countUpdated = 0;
    protected int $countSkipped = 0;

    public function handle()
    {
        $this->apiToken = config('vatsim-api.key');
        $this->apiRequest = Http::withHeaders([
            'Authorization' => "Token {$this->apiToken}",
        ]);

        $response = $this->apiRequest->get(config('vatsim-api.base').'divisions/GBR/members/?paginated');
        $this->info("Total of {$response->collect()->get('count')} members to process.");

        collect($response->collect()->get('results'))->each(function ($member) {
            $this->process($member);
        });

        // Process paginated results
        while ($response->successful() && $response->collect()->get('next') != null) {
            $response = $this->apiRequest->get($response->collect()->get('next'));

            collect($response->collect()->get('results'))->each(function ($member) {
                $this->process($member);
            });
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
            'region' => 'required|string',
            'division' => 'required|string',
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
        $account->updateDivision($member['division'], $member['region']);

        $account->wasRecentlyCreated ?? $account->notify(new WelcomeMember());
        $account->wasRecentlyCreated ? $this->countNewlyCreated++ : $this->countUpdated++;
    }
}
