<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use App\Notifications\Mship\WelcomeMember;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\PendingRequest;

class ImportMembers extends Command
{
    protected $signature = 'Members:CertImport';

    protected $description = 'Import VATSIM UK members from the VATSIM API.';

    protected string $apiToken;
    protected PendingRequest $apiRequest;

    protected int $countNewlyCreated = 0;
    protected int $countUpdated = 0;
    protected int $countSkipped = 0;

    protected int $currentPage = 1;
    protected int $totalPages = 0;

    public function handle()
    {
        $this->apiToken = config('vatsim-api.key');
        $this->apiRequest = Http::withHeaders([
            'Authorization' => "Token {$this->apiToken}",
        ]);

        $response = $this->apiRequest->get(config('vatsim-api.base').'divisions/GBR/members/?paginated');

        $this->info("Total of {$response->collect()->get('count')} members to process.");
        $this->totalPages = round($response->collect()->get('count') / 1000, 0, PHP_ROUND_HALF_UP) + 1;

        $this->info("Processing page {$this->currentPage} of {$this->totalPages}...");
        $this->withProgressBar($response->collect()->get('results'), function ($member) {
            $this->process($member);
        });

        $this->newLine();

        // Process paginated results
        while ($response->successful() && $response->collect()->get('next') != null) {
            $this->currentPage++;
            $this->info("Processing page {$this->currentPage} of {$this->totalPages}...");

            $response = $this->apiRequest->get($response->collect()->get('next'));

            $this->withProgressBar($response->collect()->get('results'), function ($member) {
                $this->process($member);
            });
            $this->newLine();
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
                'cert_checked_at' => now()
            ]
        );

        $account->updateVatsimRatings($member['rating'], $member['pilotrating']);
        $account->updateDivision($member['division'], $member['region']);

        $account->wasRecentlyCreated ?? $account->notify(new WelcomeMember());
        $account->wasRecentlyCreated ? $this->countNewlyCreated++ : $this->countUpdated++;
    }
}
