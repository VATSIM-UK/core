<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use App\Notifications\Mship\WelcomeMember;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ImportMembers extends Command
{
    protected $signature = 'Members:CertImport';

    protected $description = 'Import VATSIM UK members from the VATSIM API.';

    protected string $apiToken;

    protected Collection $existingMembers;
    protected Collection $importedMembers;

    protected int $countNewlyCreated = 0;
    protected int $countUpdated = 0;
    protected int $countSkipped = 0;

    public function __construct()
    {
        $this->apiToken = config('vatsim-api.key');
        $this->existingMembers = DB::table('mship_account')->pluck('id');
        $this->importedMembers = collect();

        parent::__construct();
    }

    public function handle()
    {
        $this->getMembers();

        $this->info('Processing members...');

        $this->withProgressBar($this->importedMembers, function ($member) {
            $validator = Validator::make($member, [
                'id' => 'required|integer',
                'rating' => 'required|integer',
                'name_first' => 'required|string',
                'name_last' => 'required|string',
                'email' => 'required|email',
                'reg_date' => 'required|date',
            ]);

            $validator->fails() ? $this->countSkipped++ : $this->processMember($member);
        });

        $this->newLine();

        $this->info("Successfully created {$this->countNewlyCreated} new, updated {$this->countUpdated} and skipped {$this->countSkipped} members.");
    }

    protected function getMembers()
    {
        $this->info('Fetching members from VATSIM API...');

        $response = Http::withHeaders([
            'Authorization' => "Token {$this->apiToken}",
        ])->get(config('vatsim-api.base').'divisions/GBR/members');

        // Process first page of results
        foreach ($response->collect()->get('results') as $result) {
            $this->importedMembers->push($result);
        }

        // Process paginated results
        while ($response->successful() && $response->collect()->get('next') != null) {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->apiToken}",
            ])->get($response->collect()->get('next'));

            foreach ($response->collect()->get('results') as $result) {
                $this->importedMembers->push($result);
            }
        }

        $this->info("{$this->importedMembers->count()} members obtained from VATSIM API.");
    }

    protected function processMember(array $member)
    {
        $account = Account::updateOrCreate(
            ['id' => $member['id']],
            [
                'name_first' => $member['name_first'],
                'name_last' => $member['name_last'],
                'email' => $member['email'],
                'joined_at' => Carbon::create($member['reg_date']),
                'inactive' => (int) $member['rating'] < 0,
            ]
        );

        $account->wasRecentlyCreated ?? $account->notify(new WelcomeMember());
        $account->wasRecentlyCreated ? $this->countNewlyCreated++ : $this->countUpdated++;
    }
}
